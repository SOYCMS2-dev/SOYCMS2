<?php
/**
 * 全権限と、権限があればチェックボックスをつけて表示
 */
class RoleList extends HTMLList{
	
	private $roles = array();
	
	function populateItem($entity,$key){
		
		$this->addCheckbox("role_check",array(
			"name" => "role[]",
			"value" => $key,
			"label" => $entity["name"],
			"selected" => (isset($this->roles[$key]))
		));
		
		$this->addLabel("role_description",array(
			"text" => $entity["description"]
		));
	}

	function getRoles() {
		return $this->roles;
	}
	function setRoles($roles) {
		$this->roles = $roles;
	}
}
?>