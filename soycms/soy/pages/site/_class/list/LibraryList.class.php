<?php

class LibraryList extends HTMLList{
	
	private $detailLink;
	
	function init(){
		if(count($this->list)<1){
			$list = SOYCMS_Library::getList();
			$this->setList($list);
		}
		
		$this->detailLink = soycms_create_link("/page/library/");
		$this->_soy2_parent->addModel("library_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("library_not_exists",array("visible"=>count($this->list)<1));
	}

	function populateItem($entity,$key){
		
		$this->addLabel("library_id",array("text"=>$entity->getId()));
		$this->addLabel("library_name",array("text"=>$entity->getName()));
		$this->addLink("library_edit_link",array("link"=>
			$this->detailLink . "detail/?id=" .$entity->getId()));
			
		$this->addLink("library_copy_link",array("link"=>
			$this->detailLink . "create?id=" .$entity->getId()));
			
		$this->addLink("library_remove_link",array("link"=>
			$this->detailLink . "remove?id=" .$entity->getId()));
		
		$this->addLabel("library_comment",array("text"=>$entity->getDescription()));
		
		$this->addInput("library_order",array(
			"name" => "LibraryOrder[".$entity->getId()."]",
			"value" => ""
		));
		
			
	}
}
?>