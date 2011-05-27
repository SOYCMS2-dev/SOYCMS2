<?php
SOY2::import("admin.domain.SOYCMS_User");

class page_user_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["user_id"])){
			$this->jump("/user/detail/" . $_POST["user_id"]);
		}
	}

	function page_user_index() {
		WebPage::WebPage();
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		$users = $this->getUsers();
		$roles = $this->getRoles();
		
		$userList = array();
		$noRoleUserList = array();
		foreach($users as $key => $user){
			if(!isset($roles[$key])){
				$noRoleUserList[$key] = $users[$key];
				continue;
			}
			$userList[$key] = $users[$key];
		}
		
		
		$this->createAdd("user_list","_class.list.UserList",array(
			"list" => $userList,
			"roles" => $roles
		));
		
		
		
		//新規追加のフォーム
		$this->addForm("add_form");
		$this->addModel("noroleuser_exists",array(
			"visible" => (count($noRoleUserList)>0)
		));
		$this->addSelect("user_select",array(
			"name" => "user_id",
			"options" => $noRoleUserList,
			"property" => "name"
		));
	}
	
	function getRoles(){
		return SOYCMS_Role::getRoles();
	}
	
	function getUsers(){
		return SOY2DAO::find("SOYCMS_User");
	}
}
?>