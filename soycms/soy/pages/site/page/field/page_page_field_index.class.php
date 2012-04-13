<?php

class page_page_field_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["FieldOrder"]) && isset($_POST["save_order"])){
			if(isset($_GET["type"])){
				
				$configs = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($_GET["type"]);
				$res = array();
				foreach($_POST["FieldOrder"] as $value => $null){
					$res[$value] = $configs[$value];
				}
				
				SOYCMS_ObjectCustomFieldConfig::saveConfig($_GET["type"],$res);
			
			}else{
				
				$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
				$res = array();
				foreach($_POST["FieldOrder"] as $value => $null){
					$res[$value] = $configs[$value];
				}
				
				SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$res);
				
			}
			
			
			$this->jump("/page/field?updated");
		}
		
	}
	
	function init(){
		
		if(soy2_check_token() && isset($_GET["remove"]) && isset($_GET["id"])){
			$id = $_GET["id"];
			if(isset($_GET["type"])){
				$fields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($_GET["type"]);
				unset($fields[$id]);
				SOYCMS_ObjectCustomFieldConfig::saveConfig($_GET["type"],$fields);
				
				$this->jump("/page/field?updated");
			}else{
				
				$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
				unset($configs[$id]);
				SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$configs);
				
				$this->jump("/page/field?deleted");
			}
			
		}
		
	}

	function page_page_field_index() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		
		$this->addForm("entry_field_form",array(
			"action" => soycms_create_link("/page/field?type=entry")
		));
		
		$this->addForm("field_form");
		
	}
	
	function buildPage(){
		
		$this->createAdd("field_tree","FieldTreeComponent",array(
		));
		
		$this->createAdd("entry_field_list","_class.list.CustomFieldConfigList",array(
			"list" => SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry"),
			"type" => "entry"
		));
		
		$this->createAdd("field_list","_class.list.CustomFieldConfigList",array(
			"list" => SOYCMS_ObjectCustomFieldConfig::loadConfig("common"),
			"type" => "common"
		));
		
		
	}
}

SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");

class FieldTreeComponent extends PageTreeComponent{
	
	private $link;
	
	function init(){
		$this->link = soycms_create_link("page/detail/field");
		parent::init();
	}
	
	function populateItem($entity,$key,$depth,$isLast){
		
		$config = $this->getConfig($entity->getUri());
		$config_text = array();
		foreach($config as $_config){
			$config_text[] = $_config->getName();
		}
		
		$this->addModel("field_exists",array(
			"visible" => count($config) > 0
		));
		
		$this->addLabel("field_config",array(
			"text" => implode(",", $config_text)
		));
		
		$this->addLink("field_config_link",array(
			"link" => $this->link . "/" . $entity->getId() 
		));
		
		return parent::populateItem($entity,$key,$depth,$isLast);	
	}
	
	function getConfig($uri){
		return SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $uri);
	}
}
