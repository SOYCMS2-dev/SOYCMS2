<?php
/**
 * @title プラグイン一覧
 */
class page_plugin_list extends SOYCMS_WebPageBase{

	function page_plugin_list(){
		WebPage::WebPage();
		
		$manager = PluginManager::getInstance();
		
		$infos = $manager->listPlugins("site");
		
		//有効無効で並び替え
		uasort($infos,array($this,"sortPlugin"));
		
		$this->createAdd("plugin_list","_class.list.PluginList",array(
			"list" => $infos
		));
	}
	
	function sortPlugin($a,$b){
		if(($a->isActive() && $b->isActive()) ||
		   (!$a->isActive() && !$b->isActive())
		){
			return strcmp($a->getId(),$b->getId());
		}
		
		if(!$b->isActive())return -1;
		
		return 1;
	}
}