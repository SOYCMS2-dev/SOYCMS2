<?php
/*
 * アプリケーションページ拡張
 */
class SOYCMS_SiteApplicationExtension implements SOY2PluginAction{
	
	private $pageObj;	//SOYCMS_ApplicationPage
	
	/**
	 * 公開側に表示する
	 */
	function getHTML($htmlObj,$page){
		
	}
	
	function getForm($htmlObj,$page){
		
	}
	
	/**
	 * 公開側フォーム POST時
	 */
	function doPost($htmlObj,$page){
		
	}
	
	/**
	 * 設定フォーム
	 */
	function getConfigForm($page){
		
	}
	
	/* getter setter */
	
	function getPageObj() {
		return $this->pageObj;
	}
	function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}
}
class SOYCMS_SiteApplicationExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "form";
	private $pageObj;
	private $htmlObj;
	private $action;

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		$action->setPageObj($this->pageObj);
		$this->action = $action;
		
		switch($this->mode){
			case "post":
				$action->doPost($this->htmlObj,$this->pageObj);
				break;
				
			case "config":
				echo $action->getConfigForm($this->pageObj);
				break;
			
			case "form":
				echo $action->getForm($this->htmlObj,$this->pageObj);
				
			case "html":
				echo $action->getHTML($this->htmlObj,$this->pageObj);
				break;
		}
		
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getPageObj() {
		return $this->pageObj;
	}
	function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}
	function getHtmlObj() {
		return $this->htmlObj;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
}
PluginManager::registerExtension("soycms.site.application","SOYCMS_SiteApplicationExtensionDelegateAction");