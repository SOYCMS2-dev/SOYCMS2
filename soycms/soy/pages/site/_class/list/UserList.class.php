<?php

class UserList extends HTMLList{
	
	private $roles = array();
	private $permissions = array();
	private $detailLink;
	private $selected = array();
	
	function init(){
		$logic = SOY2Logic::createInstance("site.logic.workflow.RoleManager");
		$this->permissions = $logic->getPermissions();
		$this->detailLink = soycms_create_link("/user/detail/");
	}

	function populateItem($entity){
		
		$this->addCheckbox("user_check",array(
			"name" => "user_ids[]",
			"value" => $entity->getId(),
			"selected" => in_array($entity->getId(),$this->selected)
		));
		
		$this->addLabel("user_name",array("text" => $entity->getName()));
		$this->addLink("detail_link",array("link" => $this->detailLink . $entity->getId()));
		
		$roles = (isset($this->roles[$entity->getId()])) ? $this->roles[$entity->getId()] : array();
		$tmp = array();
		$permissions = $this->permissions;
		foreach($roles as $key => $role){
			if(!isset($permissions[$key]))continue;
			$tmp[] = $permissions[$key]["name"];
		}
		$this->addLabel("role_list_text",array(
			"text" => implode(" | ",$tmp)
		));
	}

	function getRoles() {
		return $this->roles;
	}
	function setRoles($roles) {
		$this->roles = $roles;
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}
?>