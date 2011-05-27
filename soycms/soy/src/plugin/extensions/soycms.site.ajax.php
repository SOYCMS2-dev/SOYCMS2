<?php
/*
 * 管理画面にページを追加する
 */
class SOYCMS_SiteAjaxExtension implements SOY2PluginAction{
	
	/**
	 * @void
	 */
	function execute(){
		
	}
}
class SOYCMS_SiteAjaxExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$action->execute();
	}
}
PluginManager::registerExtension("soycms.site.ajax","SOYCMS_SiteAjaxExtensionDelegateAction");

