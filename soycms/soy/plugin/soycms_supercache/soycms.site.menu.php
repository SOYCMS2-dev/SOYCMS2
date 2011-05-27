<?php
class SOYCMS_ContentsCacheConfigMenu extends SOYCMS_SiteMenuExtension{
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "キャッシュの設定";
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		return "site/ext/soycms_supercache";
	}
}
PluginManager::extension("soycms.site.menu.plugin","soycms_supercache","SOYCMS_ContentsCacheConfigMenu");
