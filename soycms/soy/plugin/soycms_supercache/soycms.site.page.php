<?php
class SOYCMS_ContentsCacheConfigPage extends SOYCMS_SitePageExtension{
	
	function SOYCMS_ContentsCacheConfigPage(){
		include(dirname(__FILE__) . "/inc.php");
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "コンテンツキャッシュの設定";
	}
	
	function doPost(){
		
		//save
		SOYCMS_DataSets::put("soycms_supercache.config", $_POST["config"]);
		
		//clear cache
		soycms_supercache_clear();
		
		SOY2PageController::redirect(soycms_create_link("/ext/soycms_supercache?updated"));
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		
		$config = SOYCMS_DataSets::get("soycms_supercache.config", array());
		
		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
PluginManager::extension("soycms.site.page","soycms_supercache","SOYCMS_ContentsCacheConfigPage");
