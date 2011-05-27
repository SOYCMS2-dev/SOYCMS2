<?php

class SnippetList extends HTMLList{
	
	private $mode = "editor";
	
	private $selected = array();
	private $childList = array();
	
	function init(){
		if(count($this->list)<1){
			$list = SOYCMS_Snippet::getList();
			
			foreach($list as $key => $snippet){
				if($snippet->getGroup()){
					if(!isset($this->childList[$snippet->getGroup()]))$this->childList[$snippet->getGroup()] = array();
					$this->childList[$snippet->getGroup()][$snippet->getId()] = $snippet;
					unset($list[$key]);
				}
			}
			
			$this->setList($list);
		}
		
		$this->createLink = soycms_create_link("/page/snippet/create");
		$this->detailLink = soycms_create_link("/page/snippet/detail");
		$this->removeLink = soycms_create_link("/page/snippet/remove");
		$this->_soy2_parent->addModel("snippet_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("snippet_not_exists",array("visible"=>count($this->list)<1));
		
	}

	function populateItem($entity,$key){
		
		$this->createAdd("child_snippet","SnippetList_ChildList",array(
			"list" => (isset($this->childList[$entity->getId()])) ? $this->childList[$entity->getId()] : array(),
			"detailLink" => $this->detailLink,
			"removeLink" => $this->removeLink 
		));
		
		$this->addModel("has_child_snippet",array(
			"visible" => (isset($this->childList[$entity->getId()]))
		));
		
		$name = ($this->mode == "insert") ? "insert_snippet_ids" : "append_snippet_ids";
		$this->addCheckbox("snippet_check",array(
			"name" => $name . "[]",
			"value"=> $entity->getId(),
			"selected" => in_array($entity->getId(),$this->selected)
		));
		
		$this->addLabel("snippet_id",array("text"=>$entity->getId()));
		$this->addLabel("snippet_name",array("text"=>$entity->getName()));
		$this->addLabel("snippet_class",array("text"=>$entity->getClass()));
		$this->addLink("snippet_edit_link",array("link"=>
			$this->detailLink . "?id=" .$entity->getId()));
		$this->addLink("snippet_apppend_link",array(
			"link"=> $this->createLink . "?id=" .$entity->getId(),
			"visible" => (isset($this->childList[$entity->getId()]))
		));
		$this->addLink("snippet_remove_link",array("link"=>
			$this->removeLink . "?id=" .$entity->getId()));
		
		$this->addLabel("snippet_comment",array("text"=>$entity->getDescription()));
		
		
		$name = ($this->mode == "insert") ? "InsertSnippetOrder" : "AppendSnippetOrder";
		$this->addInput("snippet_order",array(
			"name" => $name . "[".$entity->getId()."]",
			"value" => ""
		));
		
		
			
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}

class SnippetList_ChildList extends HTMLList{
	
	private $detailLink;
	private $removeLink;
	
	function populateItem($entity,$key){
		
		$this->addLabel("snippet_id",array("text"=>$entity->getId()));
		$this->addLabel("snippet_name",array("text"=>$entity->getName()));
		$this->addLink("snippet_edit_link",array("link"=>
			$this->detailLink . "?id=" .$entity->getId()));
		$this->addLink("snippet_remove_link",array("link"=>
			$this->removeLink . "?id=" .$entity->getId()));
		
		$this->addLabel("snippet_comment",array("text"=>$entity->getDescription()));
	}
	

	function getDetailLink() {
		return $this->detailLink;
	}
	function setDetailLink($detailLink) {
		$this->detailLink = $detailLink;
	}

	function getRemoveLink() {
		return $this->removeLink;
	}
	function setRemoveLink($removeLink) {
		$this->removeLink = $removeLink;
	}
}
?>