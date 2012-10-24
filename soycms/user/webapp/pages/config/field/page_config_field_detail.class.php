<?php

class page_config_field_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["config"]) && soy2_check_token()){
			$configs = Plus_UserProfile::getFields();
			$this->config = SOY2::cast($this->config,$_POST["config"]);
			$configs[$this->id] = $this->config;
			
			
			if(isset($_POST["remove"])){
				unset($configs[$this->id]);
				Plus_UserProfile::setFields($configs);
				$this->jump("config/field/?deleted");
			}
			
			Plus_UserProfile::setFields($configs);
			$this->jump("config/field/detail/" . $this->id . "?updated");
		}
		
		if(isset($_POST["view"]) && isset($_POST["save_view_config"])){
			foreach($_POST["view"] as $type => $value){
				$setting = Plus_UserProfile::getSettings($type);
				$setting[$this->id] = $value;
				Plus_UserProfile::setSettings($type,$setting);
			}
			
			$this->jump("config/field/detail/" . $this->id . "?updated");
		}
		
	}
	
	private $id;
	private $config;
	
	function init(){
		$configs = Plus_UserProfile::getFields();
		$this->config = $configs[$this->id];
	} 

	function page_config_field_detail($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->buildForm();
	}
	
	function buildForm(){
		$this->createAdd("form","_class.form.CustomFieldConfigForm",array(
			"config" => $this->config
		));
		
		$this->addLabel("field_name",array("text" => $this->config->getName()));
		
		$this->addModel("is_detail",array(
			"visible" => (!isset($_GET["layer"]))
		));
		
		$this->addForm("view_form");
		$setting = Plus_UserProfile::getSettings();
		$this->createAdd("common_type_select","_class.form.CustomFieldConfigSelect",array(
			"name" => "view[common]",
			"selected" => (isset($setting[$this->id])) ? $setting[$this->id] : 0,
			"mode" => "common"
		));
		
		$this->createAdd("group_list","page_config_field_detail_GroupList",array(
			"fieldId" => $this->id,
			"list" => SOY2DAO::find("Plus_Group")
		));
	}
}

class page_config_field_detail_GroupList extends HTMLList{
	
	private $fieldId;
	
	function populateItem($entity){
		$setting = Plus_UserProfile::getSettings($entity->getGroupId());
		
		$this->addLabel("group_name",array("text" => $entity->getName()));
		
		$this->createAdd("type_select","_class.form.CustomFieldConfigSelect",array(
			"name" => "view[".$entity->getGroupId()."]",
			"selected" => (isset($setting[$this->fieldId])) ? $setting[$this->fieldId] : 0
		));
	}
	

	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
}
?>