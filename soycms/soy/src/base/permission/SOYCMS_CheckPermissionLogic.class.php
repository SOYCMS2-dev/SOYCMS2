<?php

class SOYCMS_CheckPermissionLogic extends SOY2LogicBase{

	private $class;
	
	private $adminOK = array(
		"admin_site_index",
		"admin_site_login",
		"admin_site_check",
		"admin_site_select",
		"admin_user_profile",
	);
	private $adminNG = array(
		"admin_config",
		"admin_site_.*",
		"admin_user",
	);
	
	//必須
	private $essentials = array(
		"site_entry" => array("editor","author"),
		
		"site_page" => array("designer"),
		"site_menu" => array("super","designer"),
		
		"site_plugin" => array("super"),
		"site_manager" => array("super"),
		"site_config" => array("super"),
		"site_user" => array("super"),
	);
	
	function execute(){
		
		$type = basename(dirname(SOYCMS_SCRIPT_FILENAME)) . "_" . preg_replace("/^page_/","",$this->class);
		
		//admin
		if(basename(dirname(SOYCMS_SCRIPT_FILENAME)) == "admin"){
			return $this->checkAdmin($type);
		}
		
		//初期化
		if(isset($_GET["init_site"])){
			$session = SOY2Session::get("base.session.UserLoginSession");
			$isSuper = $session->isSuperUser();
			if($isSuper){
				return true;
			}
		}
		
		$session = SOY2Session::get("site.session.SiteLoginSession");
		$myRole = $session->getRoles();
		
		foreach($this->essentials as $uri => $roles){
			if(preg_match("/$uri/",$type)){
				$count = count($roles);
				if(count(array_diff($roles,$myRole)) == $count){
					return false;
				}
			}
		}
		
		
		
		return true;
	}
	
	function checkAdmin($type){
		
		//いつでもOK
		foreach($this->adminOK as $uri){
			if(preg_match("/{$uri}/",$type)){
				return true;
			}
		}	
		
		$session = SOY2Session::get("base.session.UserLoginSession");
		$isSuper = $session->isSuperUser();
	
		//必須チェック
		foreach($this->adminNG as $uri){
			if(preg_match("/{$uri}/",$type)){
				if(!$isSuper){
					return false;
				}
			}
		}
		
		return true;
	}


	function getClass() {
		return $this->class;
	}
	function setClass($class) {
		$this->class = $class;
	}
}
?>