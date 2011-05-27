<?php

class page_ext_index extends SOYCMS_WebPageBase{
	
	private $id;
	
	function doPost(){
		PluginManager::invoke("soycms.site.page",array(
			"mode" => "post",
			"moduleId" => $this->id
		));
		
	}
	
	function init(){
		PluginManager::load("soycms.site.page");
	}
	
	function page_ext_index($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		$delegater = PluginManager::invoke("soycms.site.page",array(
			"mode" => "page",
			"moduleId" => $this->id
		));
		
		$this->addLabel("ext_title",array("text"=>$delegater->getTitle()));
		$this->addLabel("ext_page",array("html"=>$delegater->getPage()));
	}
}
?>