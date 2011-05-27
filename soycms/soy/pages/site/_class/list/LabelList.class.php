<?php

class LabelList extends HTMLList{
	
	private $detailLink;
	private $removeLink;
	private $listLink;
	
	function init(){
		$this->detailLink = soycms_create_link("/page/label/detail");
		$this->listLink = soycms_create_link("/entry/search");	
		$this->removeLink = soycms_create_link("/page/label/remove");
		
		$this->_soy2_parent->addModel("label_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("no_label",array("visible"=>count($this->list)<1));
	}
	
	function populateItem($entity){
		$config = $entity->getConfigObject();
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("label_alias","HTMLLabel",array(
			"text" => $entity->getAlias()
		));
		
		$this->createAdd("label_name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		$this->createAdd("detail_link","HTMLLink",array(
			"link" => $this->detailLink . "/" . $entity->getId()
		));
		
		$this->createAdd("list_link","HTMLLink",array(
			"link" => $this->listLink . "?labels[]=" . $entity->getId()
		));
		
		$this->createAdd("remove_link","HTMLLink",array(
			"link" => $this->removeLink . "/" . $entity->getId()
		));
		
		$this->addLabel("entry_count",array(
			"text" => SOYCMS_EntryLabel::countByLabelId($entity->getId())
		));
		
		$this->addInput("label_display_order",array(
			"name" => "DisplayOrder[".$entity->getId()."]",
			"value" => $entity->getOrder()
		));
		
		$style = "background-color:" . $config["bgcolor"] . ";color:" . $config["color"];
		
		$this->addModel("label_icon",array(
			"style" => $style
		));
	}
	
}
?>