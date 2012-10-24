<?php
//include_once(SOYCMS_ROOT_DIR . "soy/pages/site/_class/list/CustomFieldList.class.php");
SOY2::import("site.logic.field.SOYCMS_ObjectCustomFieldBuilder");

class ProfileList extends HTMLList{
	
	private $userId;
	private $formName = "ObjectCustomField";
	private $formId = "object_custom_field";
	private $values = array();
	private $setting = array();
	
	function init(){
		$this->setValues(Plus_UserProfile::getProfile($this->userId));
	}
	
	function getLogic(){
		if(!$this->_logic){
			$this->_logic = SOYCMS_ObjectCustomFieldBuilder::prepare($this->formId,$this->formName,$this->type);
		}
		return $this->_logic;
	}
	
	function getValue($key){
		$values = $this->getValues();
		if(isset($values[$key])){
			return $values[$key];
		}
		return null;
	}

	function populateItem($entity){
		$logic = $this->getLogic();
		
		$fieldId = $entity->getFieldId();
		
		$this->addLabel("field_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("field_form",array(
			"html" => $logic->buildForm($entity,@$this->values[$entity->getFieldId()])
		));
		
		if(!isset($this->setting[$fieldId])){
			return false;
		}
		
	}
	
	/* getter setter */
	
	
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	

	function getSetting() {
		return $this->setting;
	}
	function setSetting($setting) {
		$this->setting = $setting;
	}
	
	function getValues() {
		return $this->values;
	}
	function setValues($values) {
		$this->values = $values;
	}
	
	function getFormId() {
		return $this->formId;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}
	
	function getFormName() {
		return $this->formName;
	}
	function setFormName($formName) {
		$this->formName = $formName;
	}
}
?>