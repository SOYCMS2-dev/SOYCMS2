<?php

class page_page_field_select extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["fields"])){
			$list = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			if(!$this->type)$this->type = "entry";
			$configs = ($this->type) ? SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($this->type) : array();
			
			foreach($_POST["fields"] as $key){
				$configs[$key] = $list[$key];
			}
			
			SOYCMS_ObjectCustomFieldConfig::saveConfig($this->type,$configs);
		}
		
		
		$this->jump("/page/field/select?updated&type=" . $this->type);
		
	}
	
	private $type = null;

	function page_page_field_select() {
		$this->type = @$_GET["type"];
		
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		$this->addForm("form");
	}
	
	function buildPage(){
		
		$list = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
		$configs = ($this->type) ? SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($this->type) : array();
		$entry = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry");
		
		$res = array();
		foreach($list as $key => $value){
			if(isset($configs[$key]))continue;
			if(isset($entry[$key]))continue;
			$res[$key] = $value;
		}
		
		$this->createAdd("field_list","_class.list.CustomFieldConfigList",array(
			"list" => $res,
			"type" => "common"
		));
		
		$this->addModel("field_empty",array(
			"visible" => count($res) == 0
		));
		
	}
	
	function getLayout(){
		return "layer.php";
	}
}
