<?php
SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");

class LabelTreeComponent extends PageTreeComponent{
	
	private $labels = array();
	
	function init(){
		parent::init();
		
		$this->setType("detail");
	}
	
	function populateItem($entity,$key,$depth,$isLast){
		
		//labels
		$labels = (isset($this->labels[$key])) ? $this->labels[$entity->getId()] : array();
		
		$this->addModel("label_exists",array("visible" => count($labels) > 0));
		
		$this->createAdd("label_list","_class.list.LabelList",array(
			"list" => $labels
		));
		
		
		return parent::populateItem($entity,$key,$depth,$isLast);	
	}


	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
}
