<?php
/*
 * 管理画面のメニューを拡張する
 */
class SOYCMS_SiteMenuExtension implements SOY2PluginAction{
	
	/**
	 * @return string
	 */
	function getTitle(){
		
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		
	}
}
class SOYCMS_SiteMenuExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $menus = array();

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$type = str_replace("soycms.site.menu.","",$extensionId); 
		
		if($type){
			if(!isset($this->menus[$type]))$this->menus[$type] = array();
			$this->menus[$type][] = array($action->getTitle(),$action->getLink());
			$action = null;
		}
	}
	

	function getMenus() {
		return $this->menus;
	}
	function setMenus($menus) {
		$this->menus = $menus;
	}
}
PluginManager::registerExtension("soycms.site.menu.*","SOYCMS_SiteMenuExtensionDelegateAction");
