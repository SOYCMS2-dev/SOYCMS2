<?php
/*
 * Created on 2011/01/16
 * ListPage Extensions
 */
class SOYCMS_ListPageExtension implements SOY2PluginAction{
	
	/**
	 * 記事の取得
	 */
	function getEntries($from,$to){
		
	}
	
	/**
	 * when return false, skip default execute method
	 */
	function onExecute($block,$htmlObj){
		return true;	
	}
	
	/**
	 * カスタマイズ画面
	 */
	function getConfigForm($page){
		return "";
	}
	
	/**
	 * モジュール名称
	 */
	function getTitle(){
		return get_class($this);
	}
	
	/**
	 * 基本の要素で表示するものを選択
	 */
	function getRequireItems($items){
		return $items;
	}

}
class SOYCMS_ListPageExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $list = array();
	private $page = null;
	private $moduleId;
	private $module;
	private $config = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($moduleId == $this->moduleId){
			$this->setModule($action);
		}
		
		switch($this->mode){
			case "block":
				if($moduleId == $this->moduleId){
					SOY2::cast($action,$this->config);
					$this->setModule($action);
					return;
				}
			case "form":
				if($moduleId == $this->moduleId){
					SOY2::cast($action,$this->config);
					echo $action->getConfigForm($this->page);
					return;
				}
				break;
			default:
			case "list":
				$this->list[$moduleId] = $action->getTitle();
				break;
		
		}
		
	}
	
	function invokeExecute($block,$htmlObj){
		if(!$this->module)return true;
		return $this->module->onExecute($block,$htmlObj);
	}
	
	function getEntries($from,$to){
		if(!$this->module)return array();
		return $this->module->getEntries($from,$to);
	}
	
	/* getter setter */
	

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
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

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
PluginManager::registerExtension("soycms.site.page.list","SOYCMS_ListPageExtensionDelegateAction");
?>