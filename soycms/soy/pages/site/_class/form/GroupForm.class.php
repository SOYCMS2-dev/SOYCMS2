<?php

class GroupForm extends HTMLForm{
	
	private $group;
	
	function init(){
		
	}

	function execute() {
		$this->buildForm($this->getGroup());
		parent::execute();
		
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($group){
		
		$this->addInput("group_id",array(
			"name" => "Group[groupId]",
			"value" => $group->getGroupId()
		));	
		
		$this->addInput("group_name",array(
			"name" => "Group[name]",
			"value" => $group->getName()
		));
		
		$this->addTextArea("group_description",array(
			"name" => "Group[description]",
			"value" => $group->getDescription()
		));	
		
		$this->createAdd("group_create_date","_class.component.SimpleDateLabel",array(
			"text" => $group->getCreateDate()
		));
		
		$this->createAdd("group_update_date","_class.component.SimpleDateLabel",array(
			"text" => $group->getCreateDate()
		));
	}

	function getGroup() {
		return $this->group;
	}
	function setGroup($group) {
		$this->group = $group;
	}
}

?>