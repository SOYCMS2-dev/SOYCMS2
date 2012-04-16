<?php
/*
 * ページの公開状態の拡張
 */
class SOYCMS_PagePermissionExtension implements SOY2PluginAction{
	
	function getName(){
		return "";
	}
	
	/**
	 * @return boolean
	 */
	function check($pageId){
		
	}
	
	/**
	 * ページ
	 */
	function getForm($pageId){
		
	}
	
	/**
	 * 
	 */
	function doPost($pageId){
		
	}
	
	/**
	 * 
	 */
	function clear($pageId){
		
	}
	
}
class SOYCMS_PagePermissionExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $list = array();
	private $mode = "list";
	private $moduleId = null;
	private $module = null;
	private $pageId = null;
	private $result = false;
	
	function run($extensionId,$moduleId,SOY2PluginAction $module){
		
		switch($this->mode){
			case "post":
				if($moduleId == $this->moduleId){
					$module->doPost($this->pageId);
				}else{
					$module->clear($this->pageId);
				}
				break;
			case "list":
				$this->list[$moduleId] = array(
					"name" => $module->getName(),
					"form" => $module->getForm($this->pageId)
				);
				break;
			case "page":
				if($moduleId == $this->moduleId){
					if($module->check($this->pageId)){
						$this->result = true;
					}
				}
				break;
		}
		
	}
	
	function getResult(){
		return $this->result;
	}


	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
	function getModule() {
		return $this->module;
	}
	function setModule($module) {
		$this->module = $module;
	}
	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}
PluginManager::registerExtension("soycms.site.page.permisson","SOYCMS_PagePermissionExtensionDelegateAction");

