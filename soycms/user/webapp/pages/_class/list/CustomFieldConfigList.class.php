<?php

class CustomFieldConfigList extends HTMLList{
	
	private $types = array();
	private $link = null;
	private $groups = array();

	function getTypes() {
		return $this->types;
	}
	function setTypes($types) {
		$this->types = $types;
	}
	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link = $link;
	}

	function init(){
		$this->types = SOYCMS_ObjectCustomFieldConfig::getTypes();
		$this->link = soycms_create_link("config/field/");
		$this->groups = SOY2DAO::find("Plus_Group");
	}
	
	function getFieldTypes($id){
		$res = array();
		return implode(",",$res);
	}
	
	function populateItem($entity,$key){
		
		
		$this->addLabel("list_field_id",array("text"=>$entity->getFieldId()));
		$this->addLabel("list_field_name",array("text"=>$entity->getName()));
		$this->addLabel("list_field_label",array("text"=>$entity->getLabel()));
		$this->addLabel("list_field_type",array("text"=>
			$this->types[$entity->getType()] . (($entity->isMulti()) ? "*" : "" ) 
		));
		$this->addLabel("list_field_description_text",array(
			"html" => nl2br($entity->getDescription())
		));
		
		$this->addLink("field_detail_link",array(
			"link" => $this->link ."detail/" . $entity->getFieldId()
		));
		$this->addLink("field_detail_popup_link",array(
			"link" => $this->link ."detail/" . $entity->getFieldId() . "?layer"
		));
		
		$this->addActionLink("field_remove_link",array(
			"link" => $this->link ."?remove&id=" . $entity->getFieldId()
		));
		$this->addActionLink("field_clear_link",array(
			"link" => $this->link ."?remove&id=" . $entity->getFieldId()
		));
		
		$this->addCheckbox("field_check",array("name" => "fields[]", "value"=>$entity->getFieldId()));
		
		$this->addLabel("order_input",array(
			"name" => "FieldOrder[{$entity->getFieldId()}]"
		));
		
		$this->addLink("toggle_config",array(
			"link" => "#column_config_" . $entity->getFieldId(),
			"visible" => $entity->getEditable()
			
		));
		$this->addModel("column_config",array(
			"attr:id" => "column_config_" . $entity->getFieldId()
		));
		
		$this->addInput("delete_button",array(
			"name" => "delete_column[{$entity->getFieldId()}]",
			"value" => "削除",
			"visible" => $entity->getEditable()
		));
		
		$this->addModel("editable",array(
			"visible" => $entity->getEditable()
		));
		
		$this->addLabel("list_field_directories",array(
			"text" => $this->getFieldTypes($entity->getFieldId())
		));
		
		
		if(strlen($entity->getFieldId())<1)return false;
		if(strlen($key)<1)return false;
	}
	
	
}
