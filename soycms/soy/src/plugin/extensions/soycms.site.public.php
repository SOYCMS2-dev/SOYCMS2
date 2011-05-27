<?php
/*
 * ページ公開側共通拡張
 * 	
 */
class SOYCMS_SitePublicCommonExtension implements SOY2PluginAction{
	
	private $extensionId;
	private $pageObj;	//SOYCMS_ApplicationPage
	
	/**
	 * 実行
	 */
	function execute($htmlObj,$pageObj){
		
	}
	
	/* getter setter */
	
	function getPageObj() {
		return $this->pageObj;
	}
	function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}

	function getExtensionId() {
		return $this->extensionId;
	}
	function setExtensionId($extensionId) {
		$this->extensionId = $extensionId;
	}
}
class SOYCMS_SitePublicCommonExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $pageObj;
	private $htmlObj;

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$action->setExtensionId($extensionId);
		$action->setPageObj($this->pageObj);
		
		$action->execute($this->htmlObj,$this->pageObj);
		
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
PluginManager::registerExtension("soycms.site.public.common_execute","SOYCMS_SitePublicCommonExtensionDelegateAction");
PluginManager::registerExtension("soycms.site.public.common_build","SOYCMS_SitePublicCommonExtensionDelegateAction");