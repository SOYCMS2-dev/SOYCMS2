<?php

class GroupList extends HTMLList{
	
	private $detailLink;
	private $selected = array(); 
	
	function init(){
		
		$groups = SOY2DAO::find("SOYCMS_Group");
		$this->setList($groups);
		
		$this->detailLink = soycms_create_link("/user/group/detail");
		
		$this->_soy2_parent->addModel("group_exists",array(
			"visible" => count($groups) > 0
		));
		
	}

    function populateItem($entity){
    	
    	$this->addCheckbox("group_check",array(
			"name" => "group_ids[]",
			"value" => $entity->getGroupId(),
			"selected" => (in_array($entity->getGroupId(), $this->selected))
		));
    	
    	$this->addLabel("group_id",array("text" => $entity->getGroupId()));
    	$this->addLabel("group_name",array("text" => $entity->getName()));
    	$this->addLabel("group_description",array("text" => $entity->getDescription()));
    	
    	$this->addLink("detail_link",array(
			"link" => $this->detailLink . "/" . $entity->getId() 
    	));
    	
    	
    }

    function getSelected() {
    	return $this->selected;
    }
    function setSelected($selected) {
    	$this->selected = $selected;
    }
}
?>