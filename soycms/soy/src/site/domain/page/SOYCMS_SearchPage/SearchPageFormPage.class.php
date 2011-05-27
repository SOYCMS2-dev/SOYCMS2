<?php

class SearchPageFormPage extends HTMLPage{
	
	private $obj;
	private $page;
	
	function SearchPageFormPage($obj){
		$this->obj = $obj;
		$this->page = $obj->getPage();
		HTMLPage::HTMLPage();
	}
	
	function main(){
		
		PluginManager::load("soycms.search");
		$delegate = PluginManager::invoke("soycms.search",array(
			"mode" => "list"
		));
		$modules = $delegate->getList();
		
		$this->addSelect("module_list",array(
			"name" => "object[module]",
			"selected" => $this->obj->getModule(),
			"options" => $modules 
		));
		
		$this->addInput("limit",array(
			"name" => "object[limit]",
			"value" => $this->obj->getLimit()
		));
		
		$config = PluginManager::display("soycms.search",array(
			"mode" => "form",
			"moduleId" => $this->obj->getModule(),
			"page" => $this->page
		));
		$this->addLabel("module_config",array(
			"html" => (isset($modules[$this->obj->getModule()])) ? $config : "検索モジュールが設定されていません"
		));
		
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
	
	
}
?>