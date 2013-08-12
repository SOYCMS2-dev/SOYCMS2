<?php
/**
 * 公海側Controllerクラス
 */
class SOYCMS_SiteController extends SOY2PageController{
	
	public static function getInstance(){
		return SOY2PageController::init();
	}
	
	private $pageObject;
	private $directoryObject;
	private $_binds = array();
	private $webPage;
	
	private $mode = "";
	private $ticks = array();
	
	function prepare(){
		
		if(defined("SOYCMS_MODE_BENCHMARK") && SOYCMS_MODE_BENCHMARK){
			$this->tick("init",true);
		}
		
		
		//configure SOY2DAO
		SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);
		SOY2HTMLConfig::CacheDir(SOYCMS_SITE_DIRECTORY . ".cache/");
		
		//リクエストURLから取得したサイトのURL
		define("SOYCMS_SITE_ROOT_URL",SOY2FancyURIController::createRelativeLink(".",true));
		
		//サイトのURL
		if(defined("SOYCMS_DOMAIN_ROOT") && SOYCMS_DOMAIN_ROOT){
			define("SOYCMS_SITE_URL",SOYCMS_SITE_ROOT_URL . basename(dirname(SOYCMS_INDEX_PATH)));
		}else{
			define("SOYCMS_SITE_URL", SOYCMS_SITE_ROOT_URL);
		}
		
		//タイムゾーン
		$default = @date_default_timezone_get();
		if(!$default)$default = "Asia/Tokyo";
		$timezone = SOYCMS_DataSets::get("timezone",@date_default_timezone_get());
		if($timezone != $default){
			date_default_timezone_set($timezone);
		}
		
		//invoke events
		PluginManager::load("soycms.site.controller.*");
		$delg = PluginManager::invoke("soycms.site.controller.initialize",array("controller" => $this));
		
		$this->tick("plugin.intialize");
		
		//セッションIDの引き継ぎ
		if(isset($_GET["SOYCMS_SSID"])){
			session_id($_GET["SOYCMS_SSID"]);
			if(!isset($_SESSION))@session_start();
			if(!isset($_SESSION["SOYCMS_SSID_TOKEN"]) || $_SESSION["SOYCMS_SSID_TOKEN"] != @$_GET["SOYCMS_SSID_TOKEN"]){
				session_destroy();
				$_SESSION["SOYCMS_SSID_TOKEN"] = md5(time());
				$uri = $_SERVER["REQUEST_URI"];
				$uri = substr($uri,0,strpos($uri,"?"));
				
				SOY2PageController::redirect($uri);
			}
		}
		
		/* @var $session SiteUserLoginSession */
		$session = SOY2Session::get("site.session.SiteUserLoginSession");
		if($session->getSiteId() == SOYCMS_SITE_ID){
			//ダイナミック終了
			if(isset($_GET["dynamic"]) && $_GET["dynamic"] == "off"){
				session_regenerate_id();
				$_SESSION["SOYCMS_SSID_TOKEN"] = md5(time());
				$uri = $_SERVER["REQUEST_URI"];
				$uri = substr($uri,0,strpos($uri,"?"));
				$session->setIsDynamic(false);
				
				SOY2PageController::redirect($uri);
			}
			
			define("SOYCMS_ADMIN_LOGINED", true);
			
			if(!isset($_GET["preview"]) && !isset($_GET["template_preview"])){
				if(isset($_GET["dynamic"])){
					$session->setIsDynamic(($_GET["dynamic"] != "off"));
				}
				
				//roleの引き継ぎ
				$siteSession = SOY2Session::get("site.session.SiteLoginSession");
				
				if($siteSession->hasRole("super") || $siteSession->hasRole("designer")){
					define("SOYCMS_EDIT_DYNAMIC", $session->isDynamic());
					define("SOYCMS_MANAGE_MODE", true);	//ダイナミック編集起動用スイッチ
					SOY2::import("site.public.dynamic.SOYCMS_DynamicEditHelper");
				}
				
				define("SOYCMS_ADMIN_ROOT_URL",$session->getSoycmsRoot());
				if($siteSession->hasRole("super") || $siteSession->hasRole("editor")){
					define("SOYCMS_EDIT_ENTRY", true);
				}
			}
			
			register_shutdown_function(array($this,"onShutdown"));
			
			$this->tick("prepare.dynamic");
		}
		
		//マネージモードはfalse
		if(!defined("SOYCMS_MANAGE_MODE"))define("SOYCMS_MANAGE_MODE", false);
		
		PluginManager::invoke("soycms.site.controller.prepare",array("controller" => $this));
		
		CMSExtension::prepare($_SERVER["REQUEST_URI"]);
		
		//call binds
		$uri = $_SERVER["REQUEST_URI"];
		foreach($this->_binds as $_uri => $array){
			$_uri = str_replace("/","\\/",$_uri);
			if(preg_match("/{$_uri}/",$uri)){
				foreach($array as $_array){
					call_user_func_array($_array["func"],$_array["args"]);
				}
			}
		}
		
		$this->tick("init.finish");
	}

	function execute(){
		
		$this->prepare();
		
		$this->tick("execute");
		
		if(isset($_GET["template_preview"])){
			$session = SOY2Session::get("site.session.SiteLoginSession");
			if($session->getSiteId() == SOYCMS_SITE_ID){
				return $this->templatePreview($_GET["template_preview"]);
			}
		}
		
		
		$timer = array();
		$timer[] = microtime(true);
		
		//フック
		PluginManager::invoke("soycms.site.controller.load",array("controller" => $this));
		
		$pathBuilder = $this->getPathBuilder();
		
		//パスからURIと引数に変換
		$uri  = $pathBuilder->getPath();
		$args = $pathBuilder->getArguments();
		
		
		//トップページ
		if(empty($uri)){
			if(file_exists(SOYCMS_SITE_DIRECTORY . implode("/",$args) && !is_dir(SOYCMS_SITE_DIRECTORY . implode("/",$args)))){
				echo file_get_contents(SOYCMS_SITE_DIRECTORY . implode("/",$args));
				exit;
			}
			
			$uri = "_home";
		}
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");

		try{
			$page = $dao->getByUri($uri);
			$this->setPageObject($page);
			
			$dir = ($page->isDirectory()) ? $page : $dao->getByUri($page->getParentDirectoryUri());
			$this->setDirectoryObject($dir);
			
			if($page->isDirectory() && count($args) > 0){
				//ディレクトリに記事が無い場合はindex.htmlに渡す
				if(!$this->checkEntry($page->getId(),$args)){
					$page = $dao->getByUri($page->getIndexUri());
				}
			}
			 
		}catch(Exception $e){
			return $this->onNotFound($uri,$e);
		}
		
		$this->tick("execute.finish");
		
		//Helperに渡す
		SOYCMS_Helper::set("page_id",$page->getId());
		SOYCMS_Helper::set("page_uri",$page->getUri());
		SOYCMS_Helper::set("directory_id",$dir->getId());
		SOYCMS_Helper::set("directory_uri",$dir->getUri());
		$this->displayWebPage($uri,$page,$args);
	}
	
	function displayWebPage($uri,$page,$args = array()){
		
		$this->tick("display");
		
		CMSExtension::execute($page,$args);
		
		$webPage = null; /* @var $webPage SOYCMS_SitePageBase */
		$timer = array();$start = microtime(true);
		try{
			
			$timer[] = microtime(true);
			$webPage = $page->getWebPageObject($args);
			$this->setWebPage($webPage);
			
			CMSExtension::display($page,$webPage,$args);
			
			$this->tick("display.ext.display");
			
			$timer[] = microtime(true);
			$webPage->common_build($args);
			
			$this->tick("display.common_build");
			
			$timer[] = microtime(true);
			$webPage->main($args);
			
			$this->tick("display.main");
			
			$timer[] = microtime(true);
			$webPage->common_execute();
			
			$this->tick("display.common_execute");
			
			error_reporting(0);
			
			ob_start();
			$timer[] = microtime(true);
			$webPage->display();
			$html = ob_get_contents();
			ob_end_clean();
			
			$timer[] = microtime(true);
			
			
			//管理モードオンの場合は表示を行う
			if(SOYCMS_MANAGE_MODE == true){
				if(preg_match("/<\/body>/i", $html)){
					$html = preg_replace("/<\/body>/i", SOYCMS_DynamicEditHelper::getManageMenuHTML() . "</body>", $html);
				}
			}
			
			if(defined("SOYCMS_MODE_BENCHMARK") && SOYCMS_MODE_BENCHMARK){
				
			}else{
				echo $html;
			}
			
			//終了
			PluginManager::invoke("soycms.site.controller.teardown",array("controller" => $this));
			
			$this->tick("display.finish");
			
			if(defined("SOYCMS_MODE_BENCHMARK") && SOYCMS_MODE_BENCHMARK){
				$this->printTick();
			}
			
		}catch(SOYCMS_NotFoundException $e){
			return $this->onPageNotFound($uri,$e);
		}catch(SOYCMS_EntryCloseException $e){
			return $this->onEntryCloseError($uri,$e);
		}catch(Exception $e){
			if($webPage && $webPage instanceof SOYCMS_ErrorPageBase){
				$this->onDefaultError(-1,500,$e);
				exit;
			}
			return $this->onPageError($uri,$e);
		}
	}
	
	function onPageError($uri,$e){
		PluginManager::invoke("soycms.site.controller.error",array("execption" => $e));
		$this->onDefaultError($uri,500,$e);
	}
	
	function onEntryCloseError($uri,$e){
		PluginManager::invoke("soycms.site.controller.error",array("execption" => $e));
		$this->onDefaultError($uri,403,$e);
	}
	
	function onPageNotFound($uri,$e){
		PluginManager::invoke("soycms.site.controller.notfound",array("controller" => $this,"exception" => $e));
		$this->onDefaultError($uri,404,$e);
	}
	
	function onDefaultError($uri,$type,$e){
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$page = null;
		
		while(true){
			
			if($uri == -1)break;
			
			try{
				$_uri = (!empty($uri)) ? soycms_union_uri($uri,$type . ".html") : $type . ".html";
				$page = $dao->getByUri($_uri);
				break;
				
			}catch(Exception $e){
				if(empty($uri))break;
				
				$uri = dirname($uri);
				if($uri == ".")$uri = "";
			}
		}
		
		
		try{
			if($page){
				$this->displayWebPage($uri,$page,array(
					"exception" => $e
				));
				exit;
			}
		}catch(Exception $e){
			return $this->onDefuaultError(null,500,$e);
		}
		
		
		
		$array = array(
			403 => "403 Forbidden",
			404 => "404 Not Found",
			500 => "500 Internal Server Error",
			503 => "503 Service Unavailable"
		);
		
		$header = $array[$type];
		
		header("HTTP/1.1 $header");
		
		if(file_exists(SOYCMS_SITE_DIRECTORY . "{$type}.html")){
			echo file_get_contents(SOYCMS_SITE_DIRECTORY . "{$type}.html");
			exit;
		}
		
		echo "<html><head></head><body>";
		echo "<h1>$header</h1>";
		echo $e->getMessage();
		echo "<!--" . var_dump($e) . "-->";
		echo "</body></html>";
		exit;
		
	}

	function &getPathBuilder(){
		static $builder;

		if(!$builder){
			$builder = new SOYCMS_PathInfoBuilder();
		}

		return $builder;
	}
	
	function templatePreview($id){
		
		$template = SOYCMS_Template::load($id);
		if(!$template){
			echo "error";
			exit;
		}
		
		$type = $template->getType();
		$page = new SOYCMS_Page();
		$page->setName($template->getName());
		$page->setType($type);
		$page->setTemplate($template->getId());
		$page->setConfigParam("title","#PageName#");
		
		$class = "SOYCMS_TemplatePreviewPage";
		$obj = SOY2HTMLFactory::createInstance($class,array(
			"arguments" => array("page" => $page, "arguments" => array())
		));
		
		$obj->common_build(array());
		$obj->main(array());
		$obj->common_execute(array());
		
		echo $obj->display();
		
	}
	
	/**
	 * 記事があるかどうかチェック
	 */
	function checkEntry($directory,$args){
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$alias = implode("/",$args);
		if($entryDAO->checkUri($alias,$directory)){
			return true;
		}
		return false;
	}
	
	function onShutdown(){
		$error = error_get_last();
		if ($error['type'] == E_ERROR || $error["type"] == E_PARSE || $error["type"] == E_STRICT) {
			var_dump($error);
		}
	}
	
	function getPageObject() {
		return $this->pageObject;
	}
	function setPageObject($pageObject) {
		$this->pageObject = $pageObject;
	}
	function getWebPage() {
		return $this->webPage;
	}
	function setWebPage($pageObject) {
		$this->webPage = $pageObject;
	}
	function getDirectoryObject() {
		return $this->directoryObject;
	}
	function setDirectoryObject($directoryObject) {
		$this->directoryObject = $directoryObject;
	}
	
	/**
	 * 特定のURIでイベントを発生
	 * @param string $uri
	 * @param function $func
	 * @param array $args
	 */
	function bind($uri,$func,$args = array()){
		if(!isset($this->_binds[$uri]))$this->_binds[$uri] = array();
		$this->_binds[$uri][] = array(
			"func" => $func,
			"args" => $args
		);
	}
	
	/**
	 * 特定のURIで特定のページを表示する
	 * @param string $uriRule
	 * @param string $triggerPageUri
	 */
	function bindPage($uri,$triggerPageUri){
		$this->bind($uri,create_function('$uri','$_SERVER["ORIG_PATH_INFO"] = $_SERVER["PATH_INFO"];$_SERVER["PATH_INFO"] = $uri;'),$tiggerPageUri);
	}
	
	function tick($type,$flag = false){
		if($flag || $this->ticks){
			$this->ticks[$type] = microtime(true);
		}
	}
	
	function printTick(){
		$val = null;
		echo "<dl>";
		foreach($this->ticks as $label => $_val){
			if(is_null($val)){
				$val = $_val;
				continue;
			}
			
			echo "<dt>" . $label . "</dt>";
			echo "<dd>";
			echo ($_val - $val);
			echo "</dd>";
		}
		echo "</dl>";
	}
}
