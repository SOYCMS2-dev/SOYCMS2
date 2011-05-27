<?php
SOY2::import("admin.domain.SOYCMS_User");

class page_user_workflow extends SOYCMS_WebPageBase{
	
	function doPost(){
		$logic = $this->getRoleManager();
		
		//追加
		if(isset($_POST["NewPermission"])){
			$perm = $_POST["NewPermission"];
			$logic->addPermission($perm["id"],$perm["name"],$perm["description"]);
			
			$this->reload();
		}
		
		//削除
		if(isset($_POST["remove"])){
			$array = $_POST["permission_keys"];
			$logic->clearPermission($array);
			
			$this->reload();
		}
		
		if(isset($_POST["save"]) && isset($_POST["Permission"])){
			$array = $_POST["Permission"];
			$permissions = $logic->getPublishPermissions();
			
			foreach($array as $key => $perm){
				$permissions[$key] = $perm;
			}
			$logic->savePermission($permissions);
			
			$this->reload();
		}
		
		if(isset($_POST["workflow"])){
			$logic = $this->getWorkflowManager();
			if($logic->save($_POST["workflow"])){
				$this->jump("/user/workflow#workflow");
			}
		}
			
		
	}

	function page_user_workflow() {
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		$this->buildWorkflowForm();
	}
	
	function buildPage(){
		$logic = $this->getRoleManager();
		$permissions = $logic->getPublishPermissions();
		
		$this->addForm("form");
		$this->createAdd("permission_list","_class.list.PermissionList",array(
			"list" => $permissions
		));
		
	}
	
	function buildForm(){
		$this->createAdd("add_form","_class.form.PermissionForm");
	}
	
	function buildWorkflowForm(){
		$logic = $this->getWorkflowManager();
		
		$this->addForm("workflow_form");
		$this->addTextarea("workflow_textarea",array(
			"attr:id" => "workflow_textarea",
			"name" => "workflow",
			"value" => file_get_contents($logic->getFilePath())
		));
		
		for($i=1;$i<=3;$i++){
			$this->addTextarea("workflow_example" . $i,array(
				"name" => "workflow_sample",
				"value" => $logic->loadSample($i)
			));	
		}
		
	}
	
	
	function getRoleManager(){
		$logic = SOY2Logic::createInstance("site.logic.workflow.RoleManager");
		return $logic;
	}
	
	function getWorkflowManager(){
		$logic = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		return $logic;
	}
}
?>