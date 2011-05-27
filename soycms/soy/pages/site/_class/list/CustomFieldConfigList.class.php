<?php

class CustomFieldConfigList extends HTMLList{
	
	private $type = "common";
	private $types = array();
	private $link = null;
	
	

	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
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
		$this->link = soycms_create_link("page/field/");
	}
	
	function getDirectories($fieldId){
		static $mapping = null;
		static $pages = null;
		
		if(!$mapping){
			$pages = SOYCMS_DataSets::get("site.page_mapping",array());
			$common = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry");
			foreach($common as $key => $array){
				if(!isset($mapping[$key]))$mapping[$key] = array();
				$mapping[$key][] = "entry";
			}
			
			//common
			$pages["entry"] = array(
				"name" => "共通"
			);
			
			
			
			foreach($pages as $id => $page){
				$configs = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $id);
				if($configs){
					foreach($configs as $key => $_array){
						if(!isset($mapping[$key]))$mapping[$key] = array();
						$mapping[$key][] = $id;
					}
				}
			}
		}
		$dirs = array();
		
		if(isset($mapping[$fieldId])){
			foreach($mapping[$fieldId] as $pageId){
				$dirs[] = $pages[$pageId]["name"];
			}
		}
		return implode(",",$dirs);
	}
	
	function populateItem($entity,$key){
		
		$this->createAdd("config_form","_class.form.CustomFieldConfigForm",array(
			"config" => $entity,
			"childForm" => true,
			"formName" => "fields"
		));
		
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
			"link" => $this->link ."?remove&id=" . $entity->getFieldId() . "&type=" . $this->type
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
			"text" => $this->getDirectories($entity->getFieldId())
		));
		
		
		if(strlen($entity->getFieldId())<1)return false;
		if(strlen($key)<1)return false;
	}
	
	
}
