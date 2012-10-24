<?php

class GroupCheckList extends HTMLList{
	
	private $selected = array();
	private $name = "groupIds[]";

	function populateItem($entity){
		$this->addCheckbox("group_check",array(
			"name" => $this->name,
			"value" => $entity->getId(),
			"selected" => in_array($entity->getGroupId(),$this->selected),
			"label" => $entity->getName()
		));
	}
	
	

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
}
?>