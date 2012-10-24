<?php
/**
 * サイトの初期化を行うLogic
 */
class InitLogic extends SOY2LogicBase{
	
	/**
	 * void
	 */
	function init($site){
		
		$dbDir = SOYCMSConfigUtil::get("db_dir");
		$config = $site->getConfigObject();
		
		$siteId = $site->getSiteId();
		
		//パス、URLをチェックする
		
		//DB
		if($config["dbtype"] == "sqlite"){
			$config["dsn"] = "sqlite:" . $dbDir . "site_" . $site->getSiteId() . ".db";
		}else if($config["dbtype"] == "mysql"){
			//DBを自動的に作る場合
			if($config["mysql_auto_create_database"] == 1){
				$dbname = "soycms2_" . $site->getSiteId();
				$config["database"] = $dbname;
			}else if(strlen($config["database"])<1){
				$config["database"] = "soycms_" . $site->getSiteId();
			}
			$dsn = $config["mysql_dsn"];
			if(!preg_match('/host=/',$dsn))$dsn = "host=" . $dsn;
			if(!preg_match('/^mysql:/',$dsn))$dsn = preg_replace('/^mysql:/',"",$dsn);
			$config["dsn"] = "mysql:" . $dsn . ";dbname="  . $config["database"];
		}
		
		//path
		$path = $site->getPath();
		if(strlen($path)<1){
			$path = soy2_realpath($_SERVER["DOCUMENT_ROOT"]) . $site->getSiteId() . "/";
			$site->setPath($path);
		}else{
			if($path[strlen($path)-1]!="/")$path.="/";
			$site->setPath($path);
		}
		
		//url
		$url = $site->getUrl();
		if(strlen($url)<1 && strlen($siteId)>0){
			$url = SOY2PageController::createRelativeLink("/" . $site->getSiteId() . "/",true);
			$site->setUrl($url);
		}else if(strlen($url)>0 && $url[strlen($url)-1] != "/"){
			$url .= "/";
			$site->setUrl($url);
		}
		
		$site->setConfig($config);
		
		return $site;
	}
	
	/**
	 * @return variant
	 */
	function testAll($site){
		$site = $this->init($site);
		
		if(!$site->check()){
			return "check";
		}
		
		if(SOYCMS_Site::checkSiteId($site->getSiteId())){
			return "siteid";
		}
		
		
		if(!$this->testSiteDirectory($site)){
			return "path";
		}
		if(!$this->testConnection($site)){
			return "db";
		}
		
		return true;
	}
	
	/**
	 * 設定の確認
	 */
	function testConfig($site,$config){
		if(strlen($config["upload"])<1)return false;
		if(strpos($config["upload"],".")!==false)return false;
		if($config["upload"][strlen($config["upload"])-1] != "/")return false;
		
		return true;
	}
	
	/**
	 * ディレクトリの書き込み権限
	 * @return boolean
	 */
	function testSiteDirectory($site){
		$path = $site->getPath();
		
		//soycmsのインストールディレクトリを含んでいた場合は不可
		if(strpos(SOYCMS_ROOT_DIR,$path) !== false){
			return false;
		}
		
		//存在していた場合
		if(file_exists($path)){
			return is_writable($path);
		}
		
		//親ディレクトリにダミーのディレクトリを作成
		$dir = dirname($path) . "/";
		$dirname = "_test_";
		while(file_exists($dir . $dirname)){
			$dirname = "_test_" . md5(rand() . microtime());
		}
		
		//ディレクトリ作成チェック
		if(!soy2_mkdir($dir . $dirname)){
			rmdir($dir . $dirname);
			return false;
		}
		
		$res = file_put_contents($dir . $dirname . "/testfile","testtest");
		if($res != 8){
			return false;
		}
		
		//cleanup
		@unlink($dir . $dirname . "/testfile");
		@rmdir($dir . $dirname);
		 
		return is_writable(dirname($path));
	}
	
	/**
	 * 接続チェックを行う
	 * @return boolean
	 */
	function testConnection($site){
		$config = $site->getConfigObject();
		
		//sqliteの時はOK
		if($config["dbtype"] != "mysql")return true;
		
		$dsn = $config["dsn"];
		$user = $config["user"];
		$pass = $config["pass"];
		
		//option
		SOY2DAOConfig::getOption("connection_failure");
		
		//テスト接続時にはdbnameは取り除く
		if(@$config["mysql_auto_create_database"]){
			$dsn = preg_replace('/dbname=.*?;?/',"",$dsn);
		}
		
		try{
			//make pdo
			$pdo = new PDO($dsn,$user,$pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}catch(Exception $e){
			return false;
		}
		
		try{
			//データベースを自動作成する場合
			if(@$config["mysql_auto_create_database"]){
				$dbname = "soycms2_" . $site->getSiteId();
				$pdo->exec("create database $dbname CHARACTER SET utf8");
				$config["database"] = $dbname;
			}
		}catch(Exception $e){
			//do nothing
			//MySQLで既にDBが作成されている場合と既にSOYCMSのサイトが作成されているかのチェックは
			//SQLを実際に発行して行う
		}
		
		try{
			$dbname = $config["database"];
			
			//use database
			$pdo->exec("use database $dbname");
			
			//check sql	soycms_entry table
			$sql = "select * from soycms_entry";
			$pdo->exec($sql);
			return false;
		}catch(Exception $e){
			return true;
		}
		
	}
	
	private $site;
	
	/**
	 * 初期化実行
	 */
	function initialize(SOYCMS_Site $site, $config){
		$this->site = $this->init($site);
		
		//定数の設定
		define("SOYCMS_SITE_DIRECTORY", $site->getPath());
		define("SOYCMS_SITE_ROOT_URL", $site->getUrl());
		define("SOYCMS_SITE_UPLOAD_DIR",soy2_realpath($site->getPath() . $config["upload"]));
		
		
		//configの掃除 . のつくディレクトリは駄目です。
		$config["upload"] = str_replace(".","_",@$config["upload"]);
		
		if(!$this->makeDirectory($this->site,$config)){
			echo "Failed to make directory";
			return false;
		}
		
		$targetDir = $this->site->getPath();
		
		if(!$this->generateConfigFile($this->site,$config)){
			echo "Failed to generate config file.";
			return false;
		}
		
		if(!$this->initController($this->site)){
			echo "Failed to initialize controller";
			return false;
		}
		
		if(!$this->initDataBase($site)){
			echo "Failed to initialize database";
			return false;
		}
		
		if(!$this->initDefaultIcons($this->site,$config)){
			echo "Failed to initialize default theme";
			return false;
		}
		
		//save data
		$this->site->save();
		
		//DNSの切り替え
		$configObj = $site->getConfigObject();
		
		SOY2DAOConfig::Dsn($configObj["dsn"]);
		SOY2DAOConfig::user($configObj["user"]);
		SOY2DAOConfig::password($configObj["pass"]);
		
		if(!$this->initDefaultTemplate($this->site,$config)){
			echo "Failed to initialize default template";
			return false;
		}
		
		//設定の入力
		$this->initConfig($site,$config);
		
		//基本テンプレートの場合
		if(is_numeric($config["template"])){
			$this->generatePages($site,$config);
			$this->generateEntries($site,$config);
			$this->generateBlocks($site,$config);
		}
		
		$this->initPlugins($site,$config);
		
		//戻す
		SOY2DAOConfig::Dsn(SOYCMS_DB_DSN);
		
		return true;
	}
	
	/**
	 * ディレクトリの初期化
	 */
	function makeDirectory($site,$config){
		
		//サイトディレクトリ
		$targetDir = $site->getPath();
		
		$this->makeDirectories(array(
			$targetDir,
			$targetDir . ".cache/",
			$targetDir . ".template/",
			$targetDir . ".page/",
			$targetDir . ".library/",
			$targetDir . ".navigation/",
			$targetDir . ".snippet/",
			$targetDir . ".plugin/",
			$targetDir . "themes/",
			$targetDir . "themes/icons/",
			$targetDir . $config["upload"], //"files/",	/* 設定したものに変更する */
			SOYCMS_ROOT_DIR . "content/" . $site->getSiteId() . "/"
		));

		return true;
	}
	
	/**
	 * ディレクトリを作成
	 */
	function makeDirectories($dirs){
		foreach($dirs as $dir){
			echo "soy2_mkdir $dir";
			echo " ";
			echo ( @soy2_mkdir($dir) ? "success" :  ( file_exists($dir) ? "[exists]" : "[fail]" ) );
			echo "\n";
			
			$dirname = basename($dir);
			if($dirname[0] == "."){
				file_put_contents($dir . ".htaccess","deny from all");
			}
		}
	}
	
	/**
	 * gnerate config php
	 */
	function generateConfigFile($site,$configObject){
		$siteIncludeFilePath = SOYCMSConfigUtil::get("config_dir") . "site/" . $site->getSiteId() . ".conf.php";
		if(!file_exists(SOYCMSConfigUtil::get("config_dir") . "site/"))soy2_mkdir(SOYCMSConfigUtil::get("config_dir") . "site/");
		$config = $site->getConfigObject();
		
		$tmp[] = "<?php";
		$tmp[] = "/* @generated by SOY CMS at " . date("Y-m-d H:i:s") . "*/";
		$tmp[] = "define(\"SOYCMS_SITE_DB_DSN\",\"".$config["dsn"]."\");";
		$tmp[] = "define(\"SOYCMS_SITE_DB_USER\",\"".$config["user"]."\");";
		$tmp[] = "define(\"SOYCMS_SITE_DB_PASS\",\"".$config["pass"]."\");";
		$tmp[] = "define(\"SOYCMS_SITE_DIRECTORY\",\"".soy2_realpath($site->getPath())."\");";
		$tmp[] = "define(\"SOYCMS_SITE_UPLOAD_DIR\",\"".soy2_realpath($site->getPath() . $configObject["upload"])."\");";
		
		file_put_contents($siteIncludeFilePath, implode("\n",$tmp));
		return true;
	}
	
	/**
	 * initController
	 */
	function initController($site){
		
		$siteDirectory = $site->getPath();
		$url = $site->getUrl();
		
		
		$controller = array();
		$controller[] = "<?php ";

		$tmp[] = "/* @generated by SOY CMS at " . date("Y-m-d H:i:s") . "*/";

		$configFilePath = SOYCMS_COMMON_DIR . "common.inc.php";
		$publicConfigFilePath = SOYCMS_COMMON_DIR . "conf/site/public.inc.php";
		$siteIncludeFilePath = SOYCMSConfigUtil::get("config_dir") . "site/" . $site->getSiteId() . ".conf.php";

		$controller[] = "define(\"SOYCMS_SITE_ID\",\"".$site->getSiteId()."\");";
		$controller[] = "define(\"SOYCMS_INDEX_PATH\",__FILE__);";
		$controller[] = "include_once(\"$configFilePath\");";
		$controller[] = "include_once(\"$publicConfigFilePath\");";
		$controller[] = "include_once(\"$siteIncludeFilePath\");";
		$controller[] = "SOY2PageController::run();";
		$controller[]  = "?>";


		$fp = fopen($siteDirectory."index.php","w");
		fwrite($fp,implode("\n",$controller));
		fclose($fp);
		
		//fix bug for cgi
		@chmod($siteDirectory."index.php", 0644);


		/*
		 * create htaccess
		 */
		$tmp = array();

		$tmp[] = "# @generated by SOY CMS at " . date("Y-m-d H:i:s");
		
		$res = parse_url($url);
		
		$tmp[] = "RewriteEngine on";
		$tmp[] = "RewriteCond %{HTTP:Authorization} ^(.*)";
		$tmp[] = "RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]";
		
		$tmp[] = "RewriteBase " . $res["path"];
		$tmp[] = "RewriteCond %{REQUEST_FILENAME} !-f";
		$tmp[] = "RewriteCond %{REQUEST_FILENAME}/index.php !-f";
		$tmp[] = "RewriteCond %{REQUEST_FILENAME}/index.html !-f";
		$tmp[] = "RewriteCond %{REQUEST_FILENAME}/index.htm !-f";
		$tmp[] = "RewriteCond %{REQUEST_URI} !/index.php/";
		$tmp[] = "RewriteCond %{REQUEST_URI} !.(jpg|png|jpeg|gif|css|js)$";
		$tmp[] = 'RewriteRule ^(.*)$ index.php?soycms_pathinfo=$1 [QSA,L]';
		
		file_put_contents($siteDirectory.".htaccess", implode("\n",$tmp));
		
		return true;
	}
	
	/**
	 * initDefaultTheme
	 */
	function initDefaultTheme($site){
		//ThemeDir
		$siteDirectory = $site->getPath();
		$targetDir = $siteDirectory . "themes/";
		
		$themeDir = dirname(__FILE__) . "/themes/";
		$files = soy2_scandir($themeDir);
		foreach($files as $file){
			soy2_copy($themeDir . $file, $targetDir . $file);
		}
		
		return true;
	}
	
	/**
	 * initDefaultIcons
	 */
	function initDefaultIcons($site){
		$targetDir = SOYCMS_ROOT_DIR . "content/" . $site->getSiteId() . "/";
		
		$iconDir = dirname(__FILE__) . "/icons/";
		$files = soy2_scandir($iconDir);
		foreach($files as $file){
			soy2_copy($iconDir . $file, $targetDir . $file);
		}
		
		return true;
	}
	
	/**
	 * initDefaultTemplate
	 */
	function initDefaultTemplate($site,$config){
		$this->site = $site;
		
		//ThemeDir
		$siteDirectory = $site->getPath();
		$targetDir = $siteDirectory . ".template/";
		
		//テンプレート設定
		$type = $config["template"];
		
		//スケルトンを採用する場合
		if(!is_numeric($config["template"])){
			$obj = SOYCMS_Skeleton::load($config["template"]);
			if($obj){
				return $this->applySkeleton($obj);
			}
		}
		
		if($type == 1)$type = 0;	//1を選んだ時は0と同じテンプレートを入れる
		
		$themeDir = dirname(__FILE__) . "/templates/{$type}/";
		$files = soy2_scandir($themeDir);
		foreach($files as $file){
			soy2_copy($themeDir . $file, $targetDir . $file,array($this,"convertVariables"));
		}
		
		//Library
		$siteDirectory = $site->getPath();
		$targetDir = $siteDirectory . ".library/";
		
		$themeDir = dirname(__FILE__) . "/libraries/{$type}/";
		$files = soy2_scandir($themeDir);
		foreach($files as $file){
			soy2_copy($themeDir . $file, $targetDir . $file,array($this,"convertVariables"));
		}
		
		//Navigation
		$siteDirectory = $site->getPath();
		$targetDir = $siteDirectory . ".navigation/";
		
		$themeDir = dirname(__FILE__) . "/navigations/{$type}/";
		$files = soy2_scandir($themeDir);
		foreach($files as $file){
			soy2_copy($themeDir . $file, $targetDir . $file,array($this,"convertVariables"));
		}
		
		//Snippets
		$siteDirectory = $site->getPath();
		$targetDir = $siteDirectory . ".snippet/";
		
		$themeDir = dirname(__FILE__) . "/snippets/";
		$files = soy2_scandir($themeDir);
		foreach($files as $file){
			soy2_copy($themeDir . $file, $targetDir . $file,array($this,"convertVariables"));
		}
		
		return $this->initDefaultTheme($site);
	}
	
	/**
	 * init database
	 */
	function initDataBase($site){
		$config = $site->getConfigObject();
		$dbtype = $config["dbtype"];
		$config = $site->getConfigObject();
		
		$pdo = SOY2DAO::_getDataSource($config["dsn"],$config["user"],$config["pass"]);
		$sql = file_get_contents(soy2_realpath(dirname(__FILE__)) . "sql/$dbtype.sql");
		$sqls = explode(";",$sql);

		foreach($sqls as $sql){
			try{
				if(empty($sql))continue;
				$pdo->exec($sql);
			}catch(Exception $e){
				
			}
		}
		
		return true;
	}
	
	/**
	 * テンプレートとかの定数を置換
	 */
	function convertVariables($filepath){
		if(preg_match('/\.html$/',$filepath)){
			$siteURL = $this->site->getURL();
			$array = parse_url($siteURL);
			$content = file_get_contents($filepath);
			
			
			$content = str_replace("@@SITE_PATH@@",$array["path"],$content);
			$content = str_replace("@@SITE_NAME@@",$this->site->getName(),$content);
			
			file_put_contents($filepath,$content);
		}
	}
	
	/* ここから先はサイトのDBを変更して行う */
	function initConfig($site,$config){
		SOYCMS_DataSets::put("encoding",@$config["encoding"]);
		SOYCMS_DataSets::put("timezone",@$config["timezone"]);
		SOYCMS_DataSets::put("site_name",@$site->getName());
		
		SOYCMS_DataSets::put("snippet.order",array("common_text","common_image","common_more","common_heading","common_box","common_list","common_pre","common_quote","youtube"));
		SOYCMS_DataSets::put("template.order",array("_default/index","_default/list","_default/list_a","_default/detail","_default/search","_default/inquiry"));
		SOYCMS_DataSets::put("library.order",array("global_navi","mainvisual","footer","search_box","banner-A","banner-B"));
	}
	
	function initRples(){
		$dao = SOY2DAOFactory::create("SOYCMS_RoleDAO");
		$dao->setRoles(SOYCMS_LOGIN_USER_ID,array("super","designer","editor","publisher"));
	}
	
	function generatePages($site,$config){
		$logic = SOY2Logic::createInstance("site.logic.init.InitEntryLogic");
		$logic->generatePages($site,$config);
		
	}
	
	function generateEntries($site,$config){
		$logic = SOY2Logic::createInstance("site.logic.init.InitEntryLogic");
		$logic->generateEntries($site,$config);
	}
	
	function generateBlocks($site,$config){
		$logic = SOY2Logic::createInstance("site.logic.init.InitEntryLogic");
		$logic->generateBlocks($site,$config);
	}
	
	/**
	 * プラグインを自動で有効化
	 */
	function initPlugins($site){
		$pluginDir = $site->getPath() . ".plugin/";
		
		$plugins = array(
			"soycms_simple_search",
			"soycms_simple_form",
			"soycms_common_parts",
			"soycms_entry_thumbnail_field",
			
			//2.0.3追加
			"soycms_google_sitemap",
			"soycms_redirect_manager",
			
			//2.0.6追加
			"soycms_simple_dashboard"
		);
		
		foreach($plugins as $plugin){
			file_put_contents($pluginDir . $plugin . ".active", date("Y-m-d H:i:s"));
		}
		
	}
	
	/**
	 * スケルトンを利用
	 */
	function applySkeleton($skeleton){
		$managaer = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		$managaer->importSkeleton($skeleton->getId());
		
		//entry logic
		SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic")->updatePageMapping();
		
		return true;
	}
	
	
}
?>