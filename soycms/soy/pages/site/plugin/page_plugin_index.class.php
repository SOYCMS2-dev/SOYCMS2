<?php
/**
 * @title プラグイン一覧
 */
class page_plugin_index extends SOYCMS_WebPageBase{

	function page_plugin_index(){
		
		//listに飛ばす。
		$this->jump("plugin/list");
		exit;
		
		WebPage::WebPage();
		
		$manager = PluginManager::getInstance();
		$manager->prepare(SOYCMS_SITE_DIRECTORY . ".plugin/");
		
		$infos = $manager->getPlugins();
		
		foreach($infos as $key => $info){
			try{
				$info->prepare();
			}catch(Exception $e){
				unset($infos[$key]);
			}
		}
		
		$this->createAdd("plugin_list","_class.list.PluginList",array(
			"list" => $infos
		));
	}
}