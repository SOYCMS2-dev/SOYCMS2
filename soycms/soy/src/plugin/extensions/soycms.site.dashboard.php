<?php
/*
 * Created on 2010/10/04
 * ダッシュボード拡張
 */
class SOYCMS_SiteDashBoardExtension implements SOY2PluginAction{
	
	/**
	 * @return string
	 */
	function getName(){
		return $this->getTitle();
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		
	}
	
	/**
	 * 設定画面がある場合はここに表示
	 */
	function getConfigLink(){
		
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		
	}
	
	/**
	 * @return string
	 */
	function getSubMenu(){
		
	}
}
class SOYCMS_SiteDashBoardExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $moduleId = null;
	private $list = array();
	private $page = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){	
		
		switch($this->mode){
			case "page":
				if($this->moduleId == $moduleId){
					$this->page = array(
						"title" => $action->getTitle(),
						"config" => $action->getConfigLink(),
						"page" => $action->getPage(),
						"submenu" => $action->getSubMenu()
					);
				}
				break;
			case "list":
				$this->list[$moduleId] = $action->getName();
				break;
		}
		
	}
	
	function getList(){
		return $this->list;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
	
	function getPage(){
		return $this->page;
	}

	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
}
PluginManager::registerExtension("soycms.site.dashboard","SOYCMS_SiteDashBoardExtensionDelegateAction");

