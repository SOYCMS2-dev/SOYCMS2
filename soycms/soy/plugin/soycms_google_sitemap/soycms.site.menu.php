<?php
class SOYCMS_GoogleSiteMapConfigMenu extends SOYCMS_SiteMenuExtension{
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "XMLサイトマップの設定";
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		return "site/ext/soycms_google_sitemap";
	}
}
PluginManager::extension("soycms.site.menu.plugin","soycms_google_sitemap","SOYCMS_GoogleSiteMapConfigMenu");
