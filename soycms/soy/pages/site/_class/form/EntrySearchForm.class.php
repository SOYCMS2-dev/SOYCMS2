<?php

class EntrySearchForm extends HTMLForm{
	
	var $_method = "get";
	
	function init(){
		
	}
	
	
	function execute(){
		
		$this->addInput("word",array(
			"name" => "word",
			"value" => (@$_GET["word"])
		));
		
		$this->addInput("keyword",array(
			"name" => "keyword",
			"value" => (@$_GET["keyword"])
		));
		
		$labels = $this->getLabels();
		
		$this->addSelect("label_select",array(
			"name" => "labels[]",
			"options" => $labels,
			"selected" => (@$_GET["labels"])
		));
		
		$this->addModel("label_exists",array(
			"visible" => count($labels) > 0
		));
		
		$tag =  SOYCMS_Tag::getTagList();
		if(!isset($_GET["tags"]))$_GET["tags"] = array();
		
		$this->createAdd("tags","HTMLTextArea",array(
			"name" => "tags",
			"value" => implode(" ",@$_GET["tags"])
		));
		
		$this->createAdd("tag_list","HTMLList",array(
			"list" => $tag,
			'populateItem:function($entity)' => '$this->addLabel("tag_name",array("text"=>$entity));' 
		));
		$this->addModel("tag_exists",array(
			"visible" => count($tag)>0
		));
		
		$this->addCheckbox("tag_option_and",array(
			"name" => "tagOption",
			"value" => 1,
			"selected" => (@$_GET["tagOption"] == 1)
		));
		
		$this->addCheckbox("tag_option_or",array(
			"name" => "tagOption",
			"value" => 0,
			"selected" => (@$_GET["tagOption"] == 0)
		));
		
		$workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		$workflow->load(); 
		$status = $workflow->getStatus();
		
		$this->addSelect("status_select",array(
			"name" => "status",
			"options" => $status,
			"selected" => @$_GET["status"]
		));
		
		parent::execute();
	}
	
	function getLabels(){
		$labels = SOY2DAO::find("SOYCMS_Label");
		
		$res = array();
		foreach($labels as $label){
			$res[$label->getId()] = $label->getName();
		}
		
		return $res;
	}

}


?>