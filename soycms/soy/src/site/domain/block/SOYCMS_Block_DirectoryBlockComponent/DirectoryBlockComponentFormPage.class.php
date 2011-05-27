<?php

class DirectoryBlockComponentFormPage extends HTMLPage{
	
	private $obj;
	
	function DirectoryBlockComponentFormPage($obj){
		$this->obj = $obj;
		HTMLPage::HTMLPage();
	}
	
	function main(){
		
		$directory = SOY2DAO::find("SOYCMS_Page",(array("type" => "detail")));
		$label = SOY2DAO::find("SOYCMS_Label");
		$tag =  SOYCMS_Tag::getTagList();
		
		$directories = $this->obj->getDirectories();
		
		$this->addCheckbox("current_directory",array(
			"elementId" => "current_directory",
			"name" => "object[directoryType]",
			"value" => 0,
			"selected" => $this->obj->getDirectoryType() == 0
		));
		
		$this->addCheckbox("select_directory",array(
			"elementId" => "select_directory",
			"name" => "object[directoryType]",
			"value" => 1,
			"selected" => $this->obj->getDirectoryType() == 1
		));
		
		$this->createAdd("directories","HTMLSelect",array(
			"name" => "object[directories][]",
			"options" => $directory,
			"property" => "name",
			"attr:multi" => 1,
			"selected" => $directories
		));
		
		$this->addCheckbox("include_child_dir",array(
			"elementId" => "includeChild",
			"name" => "object[includeChildDirectory]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $this->obj->getIncludeChildDirectory()
		));
		
		$this->addCheckbox("include_index",array(
			"elementId" => "includeIndex",
			"name" => "object[includeIndex]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $this->obj->getIncludeIndex()
		));
		
		$this->addCheckbox("include_child_index",array(
			"elementId" => "includeChildIndex",
			"name" => "object[includeChildIndex]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $this->obj->getIncludeChildIndex()
		));
		
		$this->addCheckbox("is_group_by_label",array(
			"elementId" => "is_group_by_label",
			"name" => "object[isGroupByLabel]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $this->obj->getIsGroupByLabel()
		));
		
		$this->addCheckbox("is_group_only_current_label",array(
			"elementId" => "is_group_only_current_label",
			"name" => "object[isGroupOnlyCurrentLabel]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => $this->obj->getIsGroupOnlyCurrentLabel()
		));
		
		$this->addInput("label_count",array(
			"name" => "object[labelLimit]",
			"value" => $this->obj->getLabelLimit()
		));
		
		$this->createAdd("labels","HTMLSelect",array(
			"name" => "object[labels][]",
			"options" => $label,
			"property" => "name",
			"attr:multi" => 1,
			"selected" => $this->obj->getLabels()
		));
		$this->addModel("label_exists",array(
			"visible" => count($label)>0
		));
		
		$this->addCheckbox("label_option_and",array(
			"name" => "object[labelOption]",
			"value" => 1,
			"selected" => ($this->obj->getLabelOption() == 1)
		));
		
		$this->addCheckbox("label_option_or",array(
			"name" => "object[labelOption]",
			"value" => 0,
			"selected" => ($this->obj->getLabelOption() == 0)
		));
		
		$this->createAdd("tags","HTMLTextArea",array(
			"name" => "object[tags]",
			"value" => implode(" ",$this->obj->getTags())
		));
		
		$this->createAdd("tag_list","HTMLList",array(
			"list" => $tag,
			'populateItem:function($entity)' => '$this->addLabel("tag_name",array("text"=>$entity));' 
		));
		$this->addModel("tag_exists",array(
			"visible" => count($tag)>0
		));
		
		$this->addCheckbox("tag_option_and",array(
			"name" => "object[tagOption]",
			"value" => 1,
			"selected" => ($this->obj->getTagOption() == 1)
		));
		
		$this->addCheckbox("tag_option_or",array(
			"name" => "object[tagOption]",
			"value" => 0,
			"selected" => ($this->obj->getTagOption() == 0)
		));
		
		$this->createAdd("countFrom","HTMLInput",array(
			"name" => "object[countFrom]",
			"value" => $this->obj->getCountFrom()
		));
		
		$this->createAdd("countTo","HTMLInput",array(
			"name" => "object[countTo]",
			"value" => $this->obj->getCountTo()
		));
		
		//ソート順
		$types = $this->obj->getSortTypes();
		for($i=0;$i<count($types);$i++){
			$key = $i+1;
			$this->addCheckbox("sort_type_".$key,array(
				"name" => "object[order]",
				"elementId" => "sort_type_" . $key,
				"value" => $types[$i],
				"selected" => ($types[$i] == $this->obj->getOrder())
			));
		}
		
		//index.htmlと記事のソート順
		for($i=1;$i<=3;$i++){
			$this->addCheckbox("index_sort_type_".$i,array(
				"name" => "object[indexOrder]",
				"elementId" => "index_sort_type_" . $i,
				"value" => $i,
				"selected" => ($i == $this->obj->getIndexOrder())
			));
		}
		
		//ラベルのオプション
		$this->buildLabelOption();
	}
	
	function buildLabelOption(){
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
		$this->addSelect("label_customfield_select",array(
			"options" => $configs,
			"property" => "label"
		));
		
		$this->createAdd("label_option_list","DirectoryBlockComponentFormPage_LabelOptionList",array(
			"list" => $this->obj->getLabelConditions(),
			"fields" => $configs
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
}

class DirectoryBlockComponentFormPage_LabelOptionList extends HTMLList{
	private $fields = array();
	function populateItem($entity,$key){
		$this->addSelect("option_field_id",array(
			"name" => "object[labelConditions][".$key."][fieldId]",
			"options" => $this->fields,
			"property" => "label",
			"value" => $entity["fieldId"],
		));
		$this->addInput("option_value",array(
			"name" => "object[labelConditions][".$key."][value]",
			"value" => $entity["value"]
		));
		$this->addSelect("option_condition",array(
			"options" => array(),
			"name" => "object[labelConditions][".$key."][condition]",
			"attr:_value" => $entity["condition"],
		));
		$this->addSelect("option_operation",array(
			"options" => array(),
			"name" => "object[labelConditions][".$key."][operation]",
			"attr:_value" => $entity["operation"],
		));
		$this->addInput("label_condition_remove",array(
			"name" => "object[labelConditions][".$key."][removed]",
			"value" => 0,
		));
		
		if(!isset($this->fields[$entity["fieldId"]])){
			return false;
		}
		if(@$entity["removed"] == 1){
			return false;
		}		
	}
	
	function setFields($array){
		$this->fields = $array;
	}
}
?>