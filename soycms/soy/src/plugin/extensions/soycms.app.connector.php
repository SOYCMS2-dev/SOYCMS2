<?php
/*
 * SOY CMS2 アプリケーション拡張
 * 管理画面にメニューを表示するための拡張
 */
class SOYCMS_AppConnector implements SOY2PluginAction{
	
	/**
	 * アプリの名称
	 */
	function getName(){
		return "";
	}
	
	/**
	 * アプリの説明
	 */
	function getDescription(){
		return "";
	}
	
	/**
	 * left menu
	 * @return array(
	 * 		"url" => "title"
	 * )
	 */
	function getMenus(){
		return array();
	}
	
	/**
	 * @return html
	 */
	function getMenu(){
		$array = $this->getMenus();
		if(!is_array($array)){
			return "";
		}
		
		ob_start();
		echo "<ul>";
		foreach($array as $key => $title){
			soycms_print_menu($key ,$title);	
		}
		$html = ob_get_contents();
		echo "</ul>";
		ob_end_clean();
		
		return $html;
	}
	
}

class SOYCMS_AppConnectorDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "role";
	private $roles = array();
	private $menus = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($this->mode == "menu"){
			$this->menus[$moduleId] = array(
				"title" => $action->getName(),
				"menu" => $action->getMenu()
			);
			return;
		}
		
		$this->roles[$moduleId] = array(
			"name" => $action->getName(),
			"description" => $action->getDescription()
		);
	}
	
	
	
	function setMode($mode){
		$this->mode = $mode;
	}
	
	function getRoles(){
		return $this->roles;
	}
	
	function getMenus(){
		return $this->menus;
	}
}

PluginManager::registerExtension("soycms.app.connector","SOYCMS_AppConnectorDelegateAction");

