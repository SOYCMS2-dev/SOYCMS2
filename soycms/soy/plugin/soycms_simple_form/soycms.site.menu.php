<?php
class SOYCMS_SimpleFormConfigMenu extends SOYCMS_SiteMenuExtension{
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "メールフォームの作成";
	}
	
	/**
	 * @return string
	 */
	function getLink(){
		return "site/page/create?type=page&page_type=app&object[applicationId]=soycms_simple_form&type_text=メールフォーム";
	}
}
PluginManager::extension("soycms.site.menu.page","soycms_simple_form","SOYCMS_SimpleFormConfigMenu");
