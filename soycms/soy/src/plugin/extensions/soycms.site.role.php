<?php
/*
 * roleを追加する拡張
 */
class SOYCMS_SiteRoleExtension implements SOY2PluginAction{
	
	function getRoles(){
		return array();
	}

}

class SOYCMS_SiteRoleExtensionDelegateAction  implements SOY2PluginDelegateAction{
	
	/**
	 * list 全Role一覧
	 */
	private $mode = "list";
	private $roles = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		$roles = $action->getRoles();
		
		foreach($roles as $roleId => $array){
			$this->roles[$roleId] = $array;
		}
		
		
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
	
	function getRoles(){
		return $this->roles;
	}
	
}


PluginManager::registerExtension("soycms.site.role","SOYCMS_SiteRoleExtensionDelegateAction");
