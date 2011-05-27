<?php

class NavigationList extends HTMLList{
	
	private $detailLink;
	
	function init(){
		if(count($this->list)<1){
			$list = SOYCMS_Navigation::getList();
			$this->setList($list);
		}
		
		$this->detailLink = soycms_create_link("/page/navigation/");
		
		$this->_soy2_parent->addModel("navigation_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("navigation_not_exists",array("visible"=>count($this->list)<1));
	}

	function populateItem($entity,$key){
		
		$this->addLabel("navigation_id",array("text"=>$entity->getId()));
		$this->addLabel("navigation_name",array("text"=>$entity->getName()));
		$this->addLabel("navigation_description",array("html"=>$entity->getDescription()));
		$this->addLink("navigation_edit_link",array("link"=>
			$this->detailLink . "detail?id=" .$entity->getId()));
		$this->addLink("navigation_copy_link",array("link"=>
			$this->detailLink . "create?id=" .$entity->getId()));
		$this->addLink("navigation_remove_link",array("link"=>
			$this->detailLink . "remove?id=" .$entity->getId()));
		
		$this->addInput("navigation_order",array(
			"name" => "NavigationOrder[".$entity->getId()."]",
			"value" => ""
		));
			
	}
}
?>