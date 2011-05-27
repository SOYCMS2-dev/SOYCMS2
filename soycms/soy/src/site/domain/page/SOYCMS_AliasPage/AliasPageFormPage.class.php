<?php

class AliasPageFormPage extends HTMLPage{
	
	private $obj;
	private $page;
	
	function AliasPageFormPage($obj){
		$this->obj = $obj;
		$this->page = $obj->getPage();
		HTMLPage::HTMLPage();
	}
	
	function main(){
		
		$this->createAdd("directory_tree","_class.list.PageTreeComponent",array(
			"type" => "detail",
			"checkboxName" => "object[directory]",
			"selected" => $this->obj->getDirectory() 
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
	
	
}
?>