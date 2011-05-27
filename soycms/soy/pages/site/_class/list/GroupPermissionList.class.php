<?php

class GroupPermissionList extends HTMLList{
	
	private $pageId;
	private $detailLink;
	private $permissions = array();
	
	function init(){
		
		$groups = SOY2DAO::find("SOYCMS_Group");
		$this->setList($groups);
		
		$this->detailLink = soycms_create_link("/user/group/detail");
		
		$this->_soy2_parent->addModel("group_exists",array(
			"visible" => count($groups) > 0
		));
		
		//permission
		
		$permission = SOY2DAO::find("SOYCMS_GroupPermission",array("pageId" => $this->pageId));
		$this->permissions = $permission;
		
	}

    function populateItem($entity){
    	
    	$this->addLabel("group_id",array("text" => $entity->getGroupId()));
    	$this->addLabel("group_name",array("text" => $entity->getName()));
    	$this->addLabel("group_description",array("text" => $entity->getDescription()));
    	
    	$this->addLink("detail_link",array(
			"link" => $this->detailLink . "/" . $entity->getId() 
    	));
    	
    	$groupId = $entity->getGroupId();
    	
    	$this->addCheckbox("is_readable",array(
    		"name" => "GroupPermission[".$entity->getGroupId()."][readable]",
    		"value" => 1,
    		"selected" => (!isset($this->permissions[$groupId]) || $this->permissions[$groupId]->isReadable())
    	));
    	
    	$this->addCheckbox("is_writable",array(
    		"name" => "GroupPermission[".$entity->getGroupId()."][writable]",
    		"value" => 1,
    		"selected" => (!isset($this->permissions[$groupId]) || $this->permissions[$groupId]->isWritable())
    	));
    	
    	
    }

    
    function setPageId($pageId) {
    	$this->pageId = $pageId;
    }
}
?>