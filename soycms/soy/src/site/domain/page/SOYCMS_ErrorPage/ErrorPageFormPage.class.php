<?php

class ErrorPageFormPage extends HTMLPage{
	
	private $obj;
	private $page;
	
	function ErrorPageFormPage($obj){
		$this->obj = $obj;
		$this->page = $obj->getPage();
		HTMLPage::HTMLPage();
	}
	
	function main(){
		$this->addSelect("header_select",array(
			"name" => "object[statusCode]",
			"selected" => $this->obj->getStatusCode(),
			"options" => $this->obj->getHeaders()
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
}
?>