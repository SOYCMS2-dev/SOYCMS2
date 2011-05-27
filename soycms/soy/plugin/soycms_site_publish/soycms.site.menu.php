<?php
class SOYCMS_SitePublishConfigMenu extends SOYCMS_SiteMenuExtension{
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "サイトの書き出し";
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		return "site/ext/soycms_site_publish";
	}
}
PluginManager::extension("soycms.site.menu.plugin","soycms_site_publish","SOYCMS_SitePublishConfigMenu");
