<?php

class page_page_field_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["NewField"])){
			$field = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$_POST["NewField"]);
			
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			$fields[$field->getFieldId()] = $field;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$fields);
			
			$type = "entry";
			if(!empty($_POST["type"])){
				
				if(is_numeric($_POST["type"])){
					try{
						$page = SOY2DAO::find("SOYCMS_Page", $_POST["type"]);
						$type = "entry-" . $page->getUri();
					}catch(Exception $e){
						$type = null;
					}
				}
				
				if($type){
					$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig($type);
					$fields[$field->getFieldId()] = $field;
					SOYCMS_ObjectCustomFieldConfig::saveConfig($type,$fields);
				}
			}
			
			
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
		$this->addCheckbox("entry_elect",array(
			"name" => "type",
			"value" => "entry",
			"selected" => (isset($_GET["type"]) && $_GET["type"] == "entry")
		));
		
		$pageId = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : "";
		if($pageId)$page = SOY2DAO::find("SOYCMS_Page",$pageId);
		$this->addLabel("selected_page_name",array(
			"text" => ($pageId) ? $page->getName() : "共通"
		));
		$this->addInput("selected_page_value",array(
			"name" => "type",
			"value" => ($pageId) ? $page->getUri() : null
		));
	}
}
?>