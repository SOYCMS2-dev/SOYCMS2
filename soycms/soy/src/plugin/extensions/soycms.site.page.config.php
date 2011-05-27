<?php
/*
 * ページディレクトリ設定画面にメニューを追加
 */
class SOYCMS_SitePageConfigExtension implements SOY2PluginAction{
	
	/**
	 * @return string
	 */
	function getTitle($page){
		
	}
	
	/**
	 * @void
	 */
	function doPost($page){
		
	}
	
	/**
	 * @return string
	 */
	function getPage($page){
		
	}
}
class SOYCMS_SitePageConfigExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "page";
	private $title;
	private $page;
	private $html;
	private $list = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		$page = $this->page;
		
		if(!$action->check($page)){
			return false;
		}
		
		if($this->mode == "page"){
			$this->html = $action->getPage($page);
			$this->title = $action->getTitle($page);
			return;
		}
		
		if($this->mode == "post"){
			return $action->doPost($page);
		}
		
		if($this->mode == "menu"){
			$this->list[$moduleId] = $action->getTitle($page);
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
		return $this->html;
	}
	function setPage($page) {
		$this->page = $page;
	}

	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}
}
PluginManager::registerExtension("soycms.site.page.config","SOYCMS_SitePageConfigExtensionDelegateAction");

