<?php

class SitePublishPlugin_FTPHelper{
	
	public static function connect($server,$port,$id,$pass,$isSecure = false){
		if($isSecure){
			$con = ftp_ssl_connect($server,$port);
		}else{
			$con = ftp_connect($server,$port);
		}
		
		if(!$con){
			return false;
		}
		
		
		$result = ftp_login($con,$id,$pass);
		if(!$result){
			return false;
		}
		
		return $con;
	}
	
	public static function close($con){
		if($con){
			ftp_close($con);
		}
	}
	
	public static function upload($con,$src,$dst){
		
		if(is_dir($src)){
			$files = soy2_scandir($src);
			@ftp_mkdir($con, $dst ."/" . basename($src));
			
			foreach($files as $file){
				self::upload(
					$con,
					$src  . "/" . $file,
					$dst  . "/" . basename($src)
				);
			}
			
		}else{
			
			ftp_put(
				$con,
				$dst . "/" . basename($src),
				$src,
				FTP_BINARY //always upload binary mode
			);
		}
		
		
	}
}

class SitePublishPlugin_Publisher{
	
	public static function prepare($siteId){
		SOYCMSConfigUtil::loadConfig("site/" . $siteId . ".conf.php");
			
		//configure SOY2DAO
		SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);
	}
	
	public static function publish(
		$siteId,
		$sitePath,
		$siteUrl,
		$targetPath,
		$targetUrl
	){
		$inst = new SitePublishPlugin_Publisher();
		$inst->publishImpl($siteId,
							$sitePath,
							$siteUrl,
							$targetPath,
							$targetUrl
		);
	}
	
	var $converts = array();
	
	function publishImpl(
		$siteId,
		$sitePath,
		$siteUrl,
		$targetPath,
		$targetUrl){
			
		$this->siteId = $siteId;
		$this->sitePath = $sitePath;
		$this->siteUrl = $siteUrl;
		$this->targetPath = $targetPath;
		$this->targetUrl = $targetUrl;
		
		//サイトの設定を読み込む
		if(!defined("SOYCMS_LOGIN_SITE_ID")){
			$this->prepare($siteId);
			
			define("SOYCMS_SITE_URL", $siteUrl);
			define("SOYCMS_SITE_ROOT_URL",$siteUrl);
		}
		
		if(!defined("SOYCMS_SITE_ID"))define("SOYCMS_SITE_ID", $siteId);
		if(!defined("SOYCMS_SITE_DIRECTORY"))define("SOYCMS_SITE_DIRECTORY", $sitePath);
		
		
		//check target direcotry
		if(!$this->checkTargetDirectory($targetPath)){
			echo "invalid target directory:" . $targetPath;
			exit;
		}
		
		//書き出し先のディレクトリの削除
		if(file_exists($targetPath))soy2_delete_dir($targetPath);
		
		//クラスの読み込み
		SOY2::imports("site.public.base.*");
		SOY2::imports("site.public.base.class.*");
		SOY2::imports("site.public.base.page.*");
		
		//全てのファイルを操作
		$pages = SOY2DAO::find("SOYCMS_Page");
		$this->entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
		
		$instance = SOYCMS_SiteController::getInstance();
		
		//mapping
		$mapping = SOYCMS_DataSets::load("site.page_mapping");
		$url_mapping = array();
		foreach($mapping as $id => $array){
			$uri = $array["uri"];
			$url_mapping[$uri] = $array["id"];
		}
		
		foreach($pages as $id => $page){
			$uri = $page->getUri();
			
			if(!$page->isDirectory()){
				$this->outputPage($page,$uri);
				
				//convert all label
				$dirId = $url_mapping[$page->getParentDirectoryUri()];
				if($dirId && $page->getType() == "list"){
					$directoryLabels = $labelDAO->getByDirectory($dirId);
					foreach($directoryLabels as $label){
						$_uri = soycms_union_uri($page->getParentDirectoryUri(),$label->getAlias());
						$this->outputPage($page,$_uri,array($label->getAlias()));
					}
				}
				
			}else{
				$this->outputDirectory($page,$uri);
			}
			
			
		}
		
		//copy all files
		$files = soy2_scandir(SOYCMS_SITE_DIRECTORY);
		
		foreach($files as $file){
			if(strpos($file, ".php") !== false)continue;
			if(realpath(SOYCMS_SITE_DIRECTORY . $file) == realpath($targetPath))continue;
			
			soy2_copy(
				SOYCMS_SITE_DIRECTORY . $file,
				$targetPath . $file
			);
		}
	}
	
	function outputPage($page,$uri,$args = array(),$is_output = true){
		$html = $this->getPageResult($page,$args);
		if($html){
			$html = $this->convert($html,$this->siteUrl,$this->targetUrl);
		}
		
		//pager html build
		$uri_quote = preg_quote(soycms_union_uri($this->targetUrl,$uri), "/");
		
		if(preg_match_all('/'.$uri_quote.'([^"\'<]+)/',$html,$tmp)){
			$url_prefix = soycms_union_uri($this->targetUrl);
			$name = (strpos(basename($uri),".") !== false) ? explode(".",basename($uri)) : array(basename($uri) ."/index");
			if(empty($name[1]))$name[1] = "html";
			$tmp = array_unique($tmp[0]);
			$tmp[] = soycms_union_uri($this->targetUrl,$uri) . "/1"; //for fix bug(1p)
			
			//convertsを一気に作成。
			foreach($tmp as $_key => $_suffix_url){
				$current_name = basename($_suffix_url);
				if($current_name[0] == "#")continue;
				
				if($current_name != "1"){
					$_uri = dirname($uri) . "/" . $name[0] . "-" .$current_name .  "." . $name[1];
				}else{
					$_uri = dirname($uri) . "/" . $name[0] . "." . $name[1];
				}
				$convert_url = soycms_union_uri($url_prefix,$_uri);
				$this->converts[$_suffix_url] = $convert_url;
			}
			
			//実際に置換
			foreach($tmp as $_key => $_suffix_url){
				$current_name = basename($_suffix_url);
				if($current_name[0] == "#")continue;
				
				$_uri = dirname($uri) . "/" . $name[0] . "-" .$current_name .  "." . $name[1];
				$convert_url = soycms_union_uri($url_prefix,$_uri);
				
				$page_args = explode("/",basename($current_name));
				if($page_args[0] == "1"){
					continue;
				}
				
				//親の引数とmerge
				if(!empty($args)){
					$page_args = array_merge($args,$page_args);
				}
				
				//無限のネスト防止
				if($is_output){
					$this->outputPage($page,$_uri,$page_args,false);
				}
				
				//親のHTMLを置換
				$html = str_replace($_suffix_url, $convert_url, $html);
				
				
			}
			
		}
		
		if($html){
			$this->saveHTML($html,$this->targetPath, $uri);
		}
	}
	
	function outputDirectory($page,$uri){
		$entries = $this->entryDAO->getByDirectory($page->getId());
		foreach($entries as $entry){
			$html = $this->getPageResult($page, explode("/",$entry->getUri()));
			if(!$html)continue;
			$html = $this->convert($html,$this->siteUrl,$this->targetUrl);
			
			//保存
			$_uri = soycms_union_uri($uri,$entry->getUri());
			$this->saveHTML($html,$this->targetPath, $_uri);
		}
	}
	
	function convert($html,$url_src,$url_dst){
		
		//convert url
		$html = str_replace($url_src,$url_dst, $html);
		
		//convert path
		$url_src = parse_url($url_src);
		$url_dst = parse_url($url_dst);
		$html = str_replace($url_src["path"],$url_dst["path"], $html);
		
		foreach($this->converts as $src => $dst){
			$html = str_replace($src,$dst, $html);
		}
		
		return $html;
	}
	
	function saveHTML($html,$targetDir,$uri){
		
		$name = $uri;
		if(strpos(basename($uri), ".") === false){
			$name .= "/index.html";
		}
		
		$filepath = $targetDir . $name;
		
		if(!file_exists(dirname($filepath))){
			echo "mkdir:" . $filepath;
			echo "\n";
			soy2_mkdir(dirname($filepath));
		}
		
		file_put_contents($filepath,$html);
	}
	
	function getPageResult($page,$args = array()){
		try{
			//Helperに渡す
			SOYCMS_Helper::set("page_id",$page->getId());
			SOYCMS_Helper::set("page_uri",$page->getUri());
			SOYCMS_Helper::set("dir_id",$page->getId());
			SOYCMS_Helper::set("directory_uri",$page->getParentDirectoryUri());
			
			$timer[] = microtime(true);
			$webPage = $page->getWebPageObject($args);
			
			$timer[] = microtime(true);
			$webPage->common_build($args);
			
			$timer[] = microtime(true);
			$webPage->main($args);
			
			$timer[] = microtime(true);
			$webPage->common_execute();
			
			ob_start();
			$webPage->display();
			$html = ob_get_clean();
			
			return $html;
		}catch(Exception $e){
			return false;
		}
		
	}
	
	
	public static function checkTargetDirectory($dir){
		
		//書き出し先がROOT_DIRECTORYの親はダメ
		if(strpos(SOYCMS_ROOT_DIR,$dir) !== false){
			return false;
		}
		
		//書き出し先がサイトのディレクトリの親はダメ
		if(strpos(SOYCMS_SITE_DIRECTORY,$dir) !== false){
			return false;
		}
		
		
		return true;
	}
}