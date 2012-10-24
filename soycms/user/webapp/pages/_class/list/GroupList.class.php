<?php

class GroupList extends HTMLList{
	
	function init(){
		$this->link = soycms_create_link("group/detail/");
	}
	
	function populateItem($entity){
		
		$this->addLabel("group_id",array("text" => $entity->getGroupId()));
		$this->addLabel("group_name",array("text" => $this->getName($entity)));
		$this->addLabel("group_description",array("text" => $entity->getConfigure("description")));
		$this->addLink("detail_link",array(
			"link" => $this->link . $entity->getId()
		));
		
	}
	
	function getName($entity){
		if(!$entity->getParent())return $entity->getName();
		$parent = $entity->getParent();
		
		$name = (isset($this->list[$parent]))
			 ? $this->list[$parent]->getName()
			 : "-----";
			 
		return "【".$name."】" . $entity->getName();
	}
	
}
?>