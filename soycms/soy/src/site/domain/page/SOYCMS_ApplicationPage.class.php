<?php

class SOYCMS_ApplicationPage extends SOYCMS_PageBase{
	
	private $applicationId = "__dummy__";
	private $config = array();
	
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事表示",
			"app_main" => "アプリケーションメイン",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
   	function getConfigPage(){
		try{
			PluginManager::load("soycms.site.application",$this->getApplicationId());
			return PluginManager::display("soycms.site.application",array(
				"pageObj" => $this->getPage(),
				"mode" => "config"
			));
		}catch(Exception $e){
			
		}
		
		
		return "";
	}

	function getApplicationId() {
		if(!$this->applicationId)return "___dummy____";
		return $this->applicationId;
	}
	function setApplicationId($applicationId) {
		$this->applicationId = $applicationId;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}

class  SOYCMS_AppPage extends SOYCMS_ApplicationPage{
	
}
?>