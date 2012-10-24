<?php

class PlusUserWebPageBase extends WebPage{
	
	private $controller;
	private $config;
	
	function prepare(){
		$this->setChildSoy2Prefix("cms");
		parent::prepare();
	}
	
	function getTemplateFilePath(){
		$class = str_replace("page_","",get_class($this));
		$lang = SOY2HTMLConfig::Language();
		if($lang){
			$path = SOYCMS_SITE_DIRECTORY . ".template/_user/{$class}/{$lang}.html";
			if(file_exists($path)){
				return $path;
			}
		}
		
		$path = SOYCMS_SITE_DIRECTORY . ".template/_user/{$class}/template.html";
		if(!file_exists($path)){
			$path = SOYCMS_ROOT_DIR . "user/webapp/template/{$class}/template.html";
		}
		
		return $path;
	}
	
	function getLayout(){
		return "_blank";
	}
	
	function buildPage(){
		
	}
	
	function buildForm(){
		
	}
	
	function getSession(){
		return SOY2Session::get("PlusUserSiteLoginSession");
	}
	
	
	function display(){
		$this->buildPage();
		$this->buildForm();
		
		if(class_exists("SOYCMS_IncludeModulePlugin")){
			$plugin = new SOYCMS_IncludeModulePlugin();
			$plugin->setWrapCode(false);
			$this->executePlugin("include",$plugin);
		}
		
		parent::display();
	}
	
	/* getter setter */

	/**
	 * @return PlusUser_SiteController
	 */
	function getController() {
		return $this->controller;
	}
	
	function setController($controller) {
		$this->controller = $controller;
	}
	
	/**
	 * @return PlusUserConfig
	 */
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>