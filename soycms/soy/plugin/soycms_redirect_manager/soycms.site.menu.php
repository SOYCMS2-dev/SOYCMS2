<?php
class SOYCMS_RedirectManagerConfigMenu extends SOYCMS_SiteMenuExtension{
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "リダイレクトの設定";
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		return "site/ext/soycms_redirect_manager";
	}
}
PluginManager::extension("soycms.site.menu.plugin","soycms_redirect_manager","SOYCMS_RedirectManagerConfigMenu");
