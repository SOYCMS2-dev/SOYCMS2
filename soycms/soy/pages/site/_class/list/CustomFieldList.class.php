<?php
SOY2::import("site.logic.field.SOYCMS_ObjectCustomFieldBuilder");
class CustomFieldList extends HTMLList{
	
	private $type = "label";	/* 未使用 */
	private $objectId;
	private $formName = "ObjectCustomField";
	private $formId = "object_custom_field";
	private $values = array();
	
	private $_logic;

	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getObjectId() {
		return $this->objectId;
	}
	function setObjectId($objectId) {
		$this->objectId = $objectId;
		
	}
	function getFormName() {
		return $this->formName;
	}
	function setFormName($formName) {
		$this->formName = $formName;
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
	
	function getLogic(){
		if(!$this->_logic){
			$this->_logic = SOYCMS_ObjectCustomFieldBuilder::prepare($this->formId,$this->formName,$this->type);
		}
		return $this->_logic;
	}

	function populateItem($entity,$key){
		$logic = $this->getLogic();
		
		$this->addModel("field_wrap",array(
			"attr:id" => "field-" . $entity->getFieldId()
		));
		
		$this->addLabel("field_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("field_description",array(
			"html" => nl2br($entity->getDescription())
		));
		
		$this->addLabel("field_label",array(
			"text" => $entity->getLabel()
		));
		$this->addLabel("field_id",array(
			"text" => $entity->getFieldId()
		));
		
		$this->addLabel("field_form",array(
			"html" => $logic->buildForm($entity,@$this->values[$entity->getFieldId()])
		));
		
		$this->addModel("is_multi",array(
			"visible" => $entity->isMulti()
		));
		
		
	}
}
?>