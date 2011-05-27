<?php

class page_page_field_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["NewField"])){
			$field = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$_POST["NewField"]);
			
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			$fields[$field->getFieldId()] = $field;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$fields);
			
			$type = (empty($_POST["type"])) ? "entry" : "entry-" . $_POST["type"];
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig($type);
			$fields[$field->getFieldId()] = $field;
			SOYCMS_ObjectCustomFieldConfig::saveConfig($type,$fields);
			
			
			if(isset($_GET["page"])){
				$this->jump("page/field/detail/" . $field->getFieldId() . "?created&layer");
			}else{
				$this->jump("page/field?updated");
			}
		}
		
		
	}

	function page_page_field_create() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		
	}
	
	function buildPage(){
		$this->addForm("new_field_form");
		
		
		$this->addSelect("field_type_select",array(
			"name" => "NewField[type]",
			"options" => SOYCMS_ObjectCustomFieldConfig::getTypes()
		));
		
		$this->createAdd("field_tree","_class.list.PageTreeComponent",array(
			"checkboxName" => "type", 
			"visible" => (!isset($_GET["page"]))
		));
		
		$this->addModel("page_selected",array(
			"visible" => (isset($_GET["page"]))
		));
		$this->addModel("page_not_selected",array(
			"visible" => (!isset($_GET["page"]))
		));
		
		$pageId = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : "";
		$this->addLabel("selected_page_name",array(
			"text" => ($pageId) ? SOY2DAO::find("SOYCMS_Page",$pageId)->getName() : "共通"
		));
		$this->addInput("selected_page_value",array(
			"name" => "type",
			"value" => $pageId
		));
	}
}
?>