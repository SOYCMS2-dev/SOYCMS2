<?php

class PermissionForm extends HTMLForm{
	
	private $permission;

	function execute(){
		$perm = $this->getPermission();
		
		$this->addInput("permission_id",array(
			"name" => "NewPermission[id]",
			"value" => @$perm["id"],
		));
		
		$this->addInput("permission_name",array(
			"name" => "NewPermission[name]",
			"value" => @$perm["name"],
		));
		
		$this->addInput("permission_description",array(
			"name" => "NewPermission[description]",
			"value" => @$perm["description"],
		));
		
		parent::execute();
	}
	
	function getPermission(){
		if(!$this->permission){
			$this->permission = array("id"=>"","name"=>"","description"=>"");
		}
		return $this->permission;	
	}

	function setPermission($permission) {
		$this->permission = $permission;
	}
}
?>