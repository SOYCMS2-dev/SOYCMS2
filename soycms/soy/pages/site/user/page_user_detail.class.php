<?php
SOY2::import("admin.domain.SOYCMS_User");
class page_user_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["role"])){
			$dao = SOY2DAOFactory::create("SOYCMS_RoleDAO");
			$dao->deleteByAdminId($this->id);
			$dao->setRoles($this->id,$_POST["role"]);
		}else{
			$dao = SOY2DAOFactory::create("SOYCMS_RoleDAO");
			$dao->deleteByAdminId($this->id);
		}
		
		//gruopの保存
		if(isset($_POST["group_ids"])){
			$dao = SOY2DAOFactory::create("SOYCMS_AdminGroupDAO");
			$dao->begin();
			
			$dao->deleteByAdminId($this->id);
			if(!is_array($_POST["group_ids"]))$_POST["group_ids"] = array();
			foreach($_POST["group_ids"] as $value){
				$adminGroup = new SOYCMS_AdminGroup();
				$adminGroup->setAdminId($this->id);
				$adminGroup->setGroupId($value);
				$dao->insert($adminGroup);
			}
			$dao->commit();
		}
		
		$this->jump("/user/detail/". $this->id . "?updated");
	}

	private $id;
	private $user;
	private $roles;
	
	function init(){
		try{
			$this->user = SOY2DAO::find("SOYCMS_User",$this->id);
			$this->roles = SOY2DAO::find("SOYCMS_Role",array("adminId" => $this->id));
			
		}catch(Exception $e){
			$this->jump("/user");
		}
	}
	
	function page_user_detail($args) {
		$this->id = @$args[0];
		WebPage::WebPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		$this->addForm("form");
		
		$this->addLabel("user_name",array(
			"text" => $this->user->getName()
		));
		
		//権限を全て取得
		$logic = $this->getRoleManager();
		$permissions = $logic->getPermissions();
		foreach($permissions as $key => $value){
			if(strpos($key,".") !== false){
				list($parent,$type) = explode(".",$key);
				if(!isset($this->roles[$parent])){
					unset($permissions[$key]);
				}
			}
		}
		
		$this->createAdd("role_list","_class.list.RoleList",array(
			"list" => $permissions,
			"roles" => $this->roles
		));
		
		$this->addModel("role_exists",array(
			"visible" => (count($this->roles) > 0)
		));
		
		$this->addModel("role_not_exists",array(
			"visible" => (count($this->roles) < 1)
		));
		
		//グループ
		$this->createAdd("group_list","_class.list.GroupList",array(
			"selected" => SOY2DAO::find("SOYCMS_AdminGroup",array("adminId" => $this->id))
		));
		
	}
	
	/**
	 * @return RoleManager
	 */
	function getRoleManager(){
		$logic = SOY2Logic::createInstance("site.logic.workflow.RoleManager");
		return $logic;
	}
}
?>