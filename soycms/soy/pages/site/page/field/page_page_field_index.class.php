<?php

class page_page_field_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
	}
	
	function init(){
		
		if(soy2_check_token() && isset($_GET["remove"]) && isset($_GET["id"])){
			$id = $_GET["id"];
			if(isset($_GET["type"])){
				$fields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($_GET["type"]);
				unset($fields[$id]);
				SOYCMS_ObjectCustomFieldConfig::saveObjectConfig($_GET["type"],$fields);
				
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
		
		$config = $this->getConfig($entity->getId());
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
	
	function getConfig($id){
		return SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $id);
	}
}
