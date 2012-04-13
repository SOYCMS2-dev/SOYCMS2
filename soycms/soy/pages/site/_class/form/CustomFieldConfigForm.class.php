<?php

class CustomFieldConfigForm extends HTMLForm{
	
	private $childForm = false;
	private $config;
	private $formName = "config";
	
	function init(){
		
		$this->_soy2_parent->addModel("is_group",array(
			"visible" => ($this->config->getType() == "group")
		));
		
		$this->_soy2_parent->addModel("is_not_group",array(
			"visible" => ($this->config->getType() != "group")
		));
		
		
		if(!$this->childForm){	
			$this->_soy2_parent->addForm("add_form");
			$this->_soy2_parent->addForm("list_form");
			$this->_soy2_parent->addSelect("new_field_type_select",array(
				"name" => "NewField[type]",
				"options" => SOYCMS_ObjectCustomFieldConfig::getChildTypes()
			));
			
			$this->_soy2_parent->createAdd("field_list","_class.list.CustomFieldConfigList",array(
				"list" => $this->config->getFields()
			));
		}
		
	}
	
	function execute(){
		$config = $this->config;
		$this->addInput("field_id_old",array(
			"name" => "field_id",
			"value" => $config->getFieldId()
		));
		
		$this->addInput("field_id",array(
			"name" => $this->formName . "[fieldId]",
			"value" => $config->getFieldId()
		));
		
		$this->addInput("field_name",array(
			"name" => $this->formName . "[name]",
			"value" => $config->getName()
		));
		
		
		$this->addInput("field_label",array(
			"name" => $this->formName . "[label]",
			"value" => $config->getLabel()
		));
		
		$this->addTextArea("field_description",array(
			"name" => $this->formName . "[config][description]",
			"value" => $config->getDescription()
		));
		
		$this->addModel("allow_multi",array(
			"visible" => $this->isAllowMulti($config->getType())
		));
		$this->addCheckbox("field_multi",array(
			"elementId" => "field_multi",
			"name" => $this->formName . "[multi]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $config->isMulti()
		));
		
		$this->addInput("field_multi_max",array(
			"name" => $this->formName . "[multiMax]",
			"value" => $config->getMultiMax()
		));
		
		$this->addSelect("field_type_select",array(
			"name" => $this->formName . "[type]",
			"options" => SOYCMS_ObjectCustomFieldConfig::getTypes(),
			"selected" => $config->getType()
		));
		$this->addSelect("child_field_type_select",array(
			"name" => $this->formName . "[type]",
			"options" => SOYCMS_ObjectCustomFieldConfig::getChildTypes(),
			"selected" => $config->getType()
		));
		
		$this->addTextArea("field_option",array(
			"name" => $this->formName . "[config][option]",
			"value" => $config->getOption()
		));
		
		$isMultiLineDefault = $this->checkMultiLineDefault($config->getType());
		$this->addInput("field_default_value",array(
			"name" => $this->formName . "[config][defaultValue]",
			"value" => $config->getDefaultValue(),
			"visible" => !$isMultiLineDefault
		));
		$this->addTextArea("field_default_text",array(
			"name" => $this->formName . "[config][defaultValue]",
			"value" => $config->getDefaultValue(),
			"visible" => $isMultiLineDefault
		));
		
		$this->addModel("has_option",array(
			"visible" => $this->checkHasOption($config->getType())
		));
		$this->addModel("has_label",array(
			"visible" => $this->checkHasLabel($config->getType())
		));
		$this->addModel("has_template",array(
			"visible" => $config->getType() == "group"
		));
		$this->addTextArea("field_template",array(
			"name" => $this->formName . "[config][template]",
			"value" => $config->getTemplate(),
		));
		$this->addCheckbox("field_template_check",array(
			"selected" => (strlen($config->getTemplate()) > 0),
			"attr:onclick" => '$("#field_template_config").toggle($(this).attr("checked"));',
			"label" => "テンプレートを使用する",
			"elementId" => "field_template_check"
		));
		
		parent::execute();
	}
	
	function checkHasOption($type){
		
		switch($type){
			case "select":
			case "radio":
			case "check":
				return true;
				break;
		}
		
		
		return false;
	}
	
	function checkHasLabel($type){
		
		switch($type){
			case "checkbox":
				return true;
				break;
		}
		
		
		return false;
		
	}
	
	function checkMultiLineDefault($type){
		
		switch($type){
			case "wysiwyg":
			case "multi";
				return true;
				break;
		}
		
		return false;
	}
	
	function isAllowMulti($type){
		
		switch($type){
			case "check":
				return false;
				break;
			
		}
		
		return true;
	}


	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(!$config instanceof SOYCMS_ObjectCustomFieldConfig){
			$config = new SOYCMS_ObjectCustomFieldConfig();
		}
		
		$this->config = $config;
	}

	function getChildForm() {
		return $this->childForm;
	}
	function setChildForm($childForm) {
		$this->childForm = $childForm;
	}

	function getFormName() {
		return $this->formName;
	}
	function setFormName($formName) {
		$this->formName = $formName;
	}
}
?>