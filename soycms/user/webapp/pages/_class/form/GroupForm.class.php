<?php

class GroupForm extends HTMLForm{
	
	private $group;
	
	function init(){
		if(!$this->group)$this->group = new Plug_Group();
	}

	function execute(){
		
		$group = $this->group;
		$config = $group->getConfigureArray();
		
		$this->addInput("group_name",array(
			"name" => "Group[name]",
			"value" => $group->getName()
		));
		
		$this->addInput("group_id",array(
			"name" => "Group[groupId]",
			"value" => $group->getGroupId()
		));
		
		$this->addTextArea("group_description",array(
			"name" => "Group[config][description]",
			"value" => @$config["description"]
		));
		
		$this->addCheckbox("group_register",array(
			"elementId" => "group_register",
			"name" => "Group[config][register]",
			"value" => 1,
			"isBoolean" => 1,
			"selected" => @$config["register"]
		));
		
		
		
		parent::execute();
	}


	function getGroup() {
		return $this->group;
	}
	function setGroup($group) {
		$this->group = $group;
	}
}
?>