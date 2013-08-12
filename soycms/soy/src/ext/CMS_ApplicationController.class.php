<?php
/**
 * 公海側Controllerクラス
 */
class CMS_ApplicationController extends SOYCMS_SiteController{
	
	public static function getInstance(){
		static $inst;
		if(!$inst)$inste = new CMS_ApplicationController();
		return $inst;
	}
	
	private $pageObject;
	private $directoryObject;
	private $_binds = array();
	private $webPage;

	function prepare(){
		
		//configure SOY2DAO
		SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);
		SOY2HTMLConfig::CacheDir(SOYCMS_SITE_DIRECTORY . ".cache/");
		
		//リクエストURLから取得したサイトのURL
		if(!defined("SOYCMS_SITE_ROOT_URL")){
			define("SOYCMS_SITE_ROOT_URL",SOY2FancyURIController::createRelativeLink(".",true));
		}
		
		//サイトのURL
		if(!defined("SOYCMS_SITE_URL")){
			if(defined("SOYCMS_DOMAIN_ROOT") && SOYCMS_DOMAIN_ROOT){
				define("SOYCMS_SITE_URL",SOYCMS_SITE_ROOT_URL . basename(dirname(SOYCMS_INDEX_PATH)));
			}else{
				define("SOYCMS_SITE_URL", SOYCMS_SITE_ROOT_URL);
			}
		}
		
		//invoke events
		PluginManager::load("soycms.site.controller.*");
		$delg = PluginManager::invoke("soycms.site.controller.initialize",array("controller" => $this));
		
		PluginManager::invoke("soycms.site.controller.prepare",array("controller" => $this));
		
		CMSExtension::prepare($_SERVER["REQUEST_URI"]);
		
	}

	function execute(){
		$this->prepare();
		
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
		
		$page = new SOYCMS_Page();
		
		//Helperに渡す
		$this->displayWebPage($uri,$page,$args);
	}
	
	function displayWebPage($uri,$page,$args = array()){
		
		CMSExtension::execute($page,$args);
		
		$webPage = null;
		$timer = array();$start = microtime(true);
		try{
			
			$timer[] = microtime(true);
			$webPage = SOY2HTMLFactory::createInstance("CMS_ApplicationControllerPage",array(
				"arguments" => array("page" => $page, "arguments" => $args)
			));
			$this->setWebPage($webPage);
			
			CMSExtension::display($page,$webPage,$args);
			
			
			$timer[] = microtime(true);
			$webPage->main($args);
			
			error_reporting(0);
			
			ob_start();
			$timer[] = microtime(true);
			$webPage->display();
			$html = ob_get_contents();
			ob_end_clean();
			
			$timer[] = microtime(true);
			
			echo $html;
			
			
			//終了
			PluginManager::invoke("soycms.site.controller.teardown",array("controller" => $this));
		
		}catch(Exception $e){
			return $this->onAppError($uri,$e);
		}
		
	}
	
	function onAppError($uri,$e){
		PluginManager::invoke("soycms.site.controller.error",array("execption" => $e));
		var_dump($e);
		exit;
	}
	

	function &getPathBuilder(){
		static $builder;

		if(!$builder){
			$builder = new SOYCMS_PathInfoBuilder();
		}

		return $builder;
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
	
}

class CMS_ApplicationControllerPage extends SOYCMS_SitePageBase{
	
	private $_layout = null;
	
	function CMS_ApplicationControllerPage($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}
	
	function setLayout($layout){
		$this->_layout = $layout;
	}
	function getLayout(){
		return $this->_layout;
	}
	
	function main($args = array()){
		$pageObj = $this->getPageObject();
		$config = $pageObj->getConfigObject();
		
		$title = (strlen(@$config["title"]) > 0) ? @$config["title"] : $pageObj->getName();
		$this->setTitle($title);
		
		//parse cms:include
		$this->parseInclude();
		
		//parse cms:navigation
		$this->parseNavigation();
		
		$this->getBodyElement();
		$this->getHeadElement();
		
		
	}
	
	function build($args){
		//do nothing
	}
	
	function parseInclude(){
		//リンクの置換え
		$plugin = new SOYCMS_IncludeModulePlugin();
		$plugin->setWrapCode(false);
		$this->executePlugin("include",$plugin);
	}
	
}
