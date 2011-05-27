<?php

class SkeletonList extends HTMLList{
	
	private $target;
	private $selected;
	
	function init(){
		$skeletons = SOYCMS_Skeleton::get();
		
		$this->setList($skeletons);
		
		$this->_soy2_parent->addModel("no_skeleton",array(
			"visible" => empty($skeletons)
		));
		
		$this->detailLink = soycms_create_link("site/skeleton/detail");
	}

    function populateItem($entity,$key){
    	
    	$this->addCheckbox("skeleton_selected",array(
			"elementId" => "skeleton_" . $key,
    		"name" => "Config[template]",
			"value" => $key,
			"selected" => ($this->selected == $key)
    	));
    	
    	$this->addModel("skeleton_selected_label",array(
    		"attr:for" => "skeleton_" . $key
    	));
    	
    	$this->addLabel("skeleton_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLink("skeleton_detail_link",array(
			"link" => $this->detailLink . "/" . $entity->getId()
		));
		
		$path = "content/skeleton/" . $entity->getId() . "/thumbnail.jpg";
		
    	$this->addImage("skeleton_thumbnail",array("src" => 
    		(file_exists(SOYCMS_ROOT_DIR . $path)) ? 
    			SOYCMS_ROOT_URL . $path : SOYCMS_ROOT_URL . "common/img/nothumb.gif",
    		"attr:alt" => "skeleton-" . $entity->getId() 
    	));
    	
    }

    function getTarget() {
    	return $this->target;
    }
    function setTarget($target) {
    	$this->target = $target;
    }

    function getSelected() {
    	return $this->selected;
    }
    function setSelected($selected) {
    	$this->selected = $selected;
    }
}
?>