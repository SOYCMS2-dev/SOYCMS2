<?php
/*
 * 記事検索用
 */
class SOYCMS_SearchExtension implements SOY2PluginAction{
	
	private $result = array();
	private $total = 0;
	private $isError = false;
	
	/**
	 * 検索実行
	 */
	function doSearch($page,$limit,$offset){
		
	}
	
	/**
	 * 検索モジュールのカスタマイズ画面
	 */
	function getConfigForm($page){
		
	}
	
	/**
	 * 検索モジュール名称
	 */
	function getTitle(){
		return get_class($this);
	}
	
	
	/* getter setter */

	function getResult() {
		return $this->result;
	}
	function setResult($result) {
		$this->result = $result;
	}
	function getTotal() {
		return $this->total;
	}
	function setTotal($total) {
		$this->total = $total;
	}

	function getIsError() {
		return $this->isError;
	}
	function setIsError($isError) {
		$this->isError = $isError;
	}
}
class SOYCMS_SearchExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "page";
	private $list = array();
	private $page = null;
	private $moduleId;
	private $module;
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			case "search":
				if($moduleId == $this->moduleId){
					$this->setModule($action);
					return;
				}
			case "form":
				if($moduleId == $this->moduleId){
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
}
PluginManager::registerExtension("soycms.search","SOYCMS_SearchExtensionDelegateAction");

