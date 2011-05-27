<?php

class RoleManager extends SOY2LogicBase{

	/**
	 * サイトの全権限を取得
	 */
	function getPermissions(){
		
		$roles = $this->getDefaultPermissions();
		
		//追加の権限を取得
		$permissions = $this->getPublishPermissions();
		$roles = array_merge($roles,$permissions);
		
		//Appの権限を取得
		PluginManager::load("soycms.app.connector");
		PluginManager::load("soycms.site.role");
		$appRoles = PluginManager::invoke("soycms.app.connector")->getRoles();
		$_roles = PluginManager::invoke("soycms.site.role")->getRoles();
		$appRoles = array_merge($appRoles,$_roles);
		ksort($appRoles);
		
		//Role拡張を追加
		$roles = array_merge($roles,$appRoles);
		
		return $roles;
	}
	
	function getDefaultPermissions(){
		//基本権限
		$roles = array(
			"super" => array("name" => "管理者","description" => "プラグイン、各種設定、ユーザの追加が出来ます。"),
			"designer" => array("name" => "サイト構築者","description" => "サイトマップの構築、デザインの編集が出来ます。"),
			"editor" => array("name" => "記事執筆","description" => "記事の編集、執筆が出来ます。"),
			/* "author" => array("name" => "運用者","description" => "記事の執筆が出来るようになります。"), */
			"publisher" => array("name" => "公開権限","description" => "記事の公開が出来るようになります。"),
		);
		return $roles;
	}
	
	function getPublishPermissions(){
		$permissions = SOYCMS_DataSets::get("role.permissions",array());
		return $permissions;
	}
	
	/**
	 * 権限の追加（追加されるのは基本的には承認を行う権限）
	 */
	function addPermission($key,$name,$description = ""){
		$permissions = SOYCMS_DataSets::get("role.permissions",array());
		$permissions[$key] = array("name" => $name, "description" => $description);
		$this->savePermission($permissions);
	}
	
	function savePermission($permissions){
		$default = $this->getDefaultPermissions();
		foreach($default as $key => $value){
			if(isset($permissions[$key])){
				unset($permissions[$key]);
			}
		}
		SOYCMS_DataSets::put("role.permissions",$permissions);
	}
	
	/**
	 * 権限の削除
	 */
	function clearPermission($key){
		$permissions = SOYCMS_DataSets::get("role.permissions",array());
		if(!is_array($key))$key = array($key);
		foreach($key as $_key){
			unset($permissions[$_key]);
		}
		$this->savePermission($permissions);
	}
	
	
}
?>