<?php

class page_config_field_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["FieldOrder"])){
			$fields = Plus_UserProfile::getFields();
			
			$config = array();
			foreach($_POST["FieldOrder"] as $key => $val){
				$config[$key] = $fields[$key];
			}
			
			Plus_UserProfile::setFields($config);
			$this->jump("/config/field?updated");
		}
	}
	
	function init(){
		
		if(soy2_check_token() && isset($_GET["remove"]) && isset($_GET["id"])){
			$id = $_GET["id"];
				
			$fields = Plus_UserProfile::getFields();
			unset($fields[$id]);
			Plus_UserProfile::setFields($fields);
			
			$this->jump("/config/field?deleted");
		}
		
		
		
		
	}

	function page_config_field_index() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		$this->addForm("form");
	}
	
	function buildPage(){
		
		$this->createAdd("field_list","_class.list.CustomFieldConfigList",array(
			"list" => Plus_UserProfile::getFields()
		));
		
		
	}
}

