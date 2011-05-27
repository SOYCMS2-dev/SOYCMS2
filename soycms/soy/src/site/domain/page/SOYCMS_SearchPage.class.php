<?php

class SOYCMS_SearchPage extends SOYCMS_PageBase{
	
	private $limit = 10;
	private $module = null;
	private $moduleConfig = array();
	
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/SearchPageFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("SearchPageFormPage",array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事",
			"entry_list" => "記事検索結果一覧",
			"pager" => "ページャー",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
	function getModuleObject(){
		
		PluginManager::load("soycms.search",$this->getModule());
		$delegate = PluginManager::invoke("soycms.search",array(
			"mode" => "search",
			"moduleId" => $this->getModule()
		));
		
		return $delegate->getModule();
		
	}
	
	/* getter setter */

	function getModule() {
		return $this->module;
	}
	function setModule($module) {
		$this->module = $module;
	}
	function getModuleConfig() {
		return $this->moduleConfig;
	}
	function setModuleConfig($moduleConfig) {
		$this->moduleConfig = $moduleConfig;
	}

	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}
}
?>