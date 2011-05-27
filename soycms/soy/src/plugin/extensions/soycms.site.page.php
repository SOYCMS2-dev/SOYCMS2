<?php
/*
 * 管理画面にページを追加する
 */
class SOYCMS_SitePageExtension implements SOY2PluginAction{
	
	private $moduleId = null;
	
	/**
	 * @return string
	 */
	function getTitle(){
		
	}
	
	/**
	 * @void
	 */
	function doPost(){
		$this->redirect();
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		
	}
	
	function redirect($suffix = "updated"){
		if($suffix)$suffix = "?" . $suffix;
		SOY2FancyURIController::jump("ext." . $this->moduleId . $suffix);
		exit;
	}
	
	function setModuleId($id){
		$this->moduleId = $id;
	}
}
class SOYCMS_SitePageExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "page";
	private $title;
	private $page;
	private $moduleId = null;
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($moduleId == $this->moduleId){
			$action->setModuleId($moduleId);
		
			if($this->mode == "post"){
				return $action->doPost();
			}
			
			$this->setTitle($action->getTitle());
			$this->setPage($action->getPage());
		}
		
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
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
}
PluginManager::registerExtension("soycms.site.page","SOYCMS_SitePageExtensionDelegateAction");

