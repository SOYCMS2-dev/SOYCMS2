<?php
/**
 * グループの追加
 */
class page_user_group_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Group"])){
			SOY2::cast($this->group,$_POST["Group"]);
			if($this->group->check()){
				$this->group->save();
			}
		}
		
		if(isset($_POST["user_ids"])){
			$dao = SOY2DAOFactory::create("SOYCMS_AdminGroupDAO");
			$dao->begin();
			
			$dao->deleteByGroupId($this->group->getGroupId());
			foreach($_POST["user_ids"] as $value){
				$adminGroup = new SOYCMS_AdminGroup();
				$adminGroup->setAdminId($value);
				$adminGroup->setGroupId($this->group->getGroupId());
				$dao->insert($adminGroup);
			}
			$dao->commit();
			
		}
		
		
		$this->jump("/user/group/detail/" . $this->group->getId() . "?updated");
		
	}
	
	function init(){
		try{
			$this->group = SOY2DAO::find("SOYCMS_Group",$this->id);
		}catch(Exception $e){
			$this->jump("/user/group");
		}
	}
	
	private $id;
	private $error = false;

	function page_user_group_detail($args) {
		$this->id = $args[0];
		
		WebPage::WebPage();
		
		$this->addModel("is_error",array(
			"visible" => $this->error
		));
		
		$this->buildPages();
		$this->buildUserList();
	}
	
	function buildPages(){
		
		$this->createAdd("form","_class.form.GroupForm",array(
			"group" => $this->group
		));		
		
	}
	
	function buildUserList(){
		
		$users = $this->getUsers();
		$roles = $this->getRoles();
		$groupUserIds = $this->getGroupUserIds();
		
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
			"roles" => $roles,
			"selected" => $groupUserIds
		));
		
		$this->addModel("user_exists",array(
			"visible" => count($users) > 0
		));
		
		$this->addLink("delete_link",array(
			"link" => soycms_create_link("/user/group/remove/" . $this->group->getId())
		));
	}
	
	
	function getGroupUserIds(){
		
		return SOY2DAO::find("SOYCMS_AdminGroup",array("groupId" => $this->group->getGroupId()));
	}
	
	
	function getRoles(){
		return SOYCMS_Role::getRoles();
	}
	
	function getUsers(){
		return SOY2DAO::find("SOYCMS_User");
	}
	
}
?>