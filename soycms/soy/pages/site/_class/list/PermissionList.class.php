<?php

class PermissionList extends HTMLList{
	
	function init(){
		
		$this->_soy2_parent->addModel("permission_list_exists",array(
			"visible" => (count($this->list))
		));
	}

	function populateItem($entity,$key){
		
		$this->addLabel("permission_key",array(
			"text" => $key
		));
		
		$this->addCheckbox("permission_check",array(
			"name" => "permission_keys[]",
			"value" => $key
		));
		
		$this->addInput("permission_name",array(
			"name" => "Permission[$key][name]",
			"value" => $entity["name"]
		));
		
		$this->addInput("permission_description",array(
			"name" => "Permission[$key][description]",
			"value" => $entity["description"]
		));
		
	}
}
?>