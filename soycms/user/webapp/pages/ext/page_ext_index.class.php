<?php

class page_ext_index extends SOYCMS_WebPageBase{
	
	private $id;
	private $args = array();
	
	function doPost(){
		PluginManager::invoke("plus.user.page",array(
			"mode" => "post",
			"moduleId" => $this->id
		));
		
	}
	
	function init(){
		PluginManager::load("plus.user.page",$this->id);
	}
	
	function page_ext_index($args) {
		$this->id = array_shift($args);
		$this->args = $args;
		WebPage::WebPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		$delegater = PluginManager::invoke("plus.user.page",array(
			"mode" => "page",
			"arguments" => $this->args
		));
		
		$this->addLabel("ext_title",array("text"=>$delegater->getTitle()));
		$this->addLabel("ext_page",array("html"=>$delegater->getPage()));
	}
}
?>