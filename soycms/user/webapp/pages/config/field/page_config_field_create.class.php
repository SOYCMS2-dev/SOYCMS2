<?php

class page_config_field_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["NewField"])){
			$field = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$_POST["NewField"]);
			
			$fields = Plus_UserProfile::getFields();
			$fields[$field->getFieldId()] = $field;
			Plus_UserProfile::setFields($fields);
			
			$this->jump("config/field?updated");
		}
		
		
	}

	function page_config_field_create() {
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
			"options" => Plus_Group::getFieldTypes()
		));
		
		$pageId = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : "";
		$this->addLabel("selected_page_name",array(
			"text" => ($pageId) ? SOY2DAO::find("SOYCMS_Page",$pageId)->getName() : "共通"
		));
		$this->addInput("selected_page_value",array(
			"name" => "type",
			"value" => $pageId
		));
		
		$this->addCheckbox("field_add_common",array(
			"elementId" => "field_add_common",
			"name" => "add_to",
			"value" => "common",
			"selected" => 1
		));
		
		$this->createAdd("group_list","page_config_field_create_GroupList",array(
			"list" => SOY2DAO::find("Plus_Group")
		));
	}
}

class page_config_field_create_GroupList extends HTMLList{
	
	function populateItem($entity){
		$this->addCheckbox("group_check",array(
			"name" => "add_to",
			"value" => $entity->getId(),
			"label" => $entity->getName(),
		));
	}
}
?>