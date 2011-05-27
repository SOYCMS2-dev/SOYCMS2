<?php
/**
 * ラベルで設定
 */
class SOYCMS_Block_ScriptBlockComponent extends SOYCMS_Block_BlockComponentBase{
	
	/**
	 * 一覧画面で表示
	 */
	function getPreview(){
		return "";
	}
	
	/**
	 * 詳細画面で表示
	 */
	function getForm(){
		
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/ScriptBlockComponentFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("ScriptBlockComponentFormPage",array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 公開側で使用
	 */
	function execute($blockComponent,$htmlObj){
		PluginManager::load("soycms.site.block");
		
		$this->delegator = PluginManager::invoke("soycms.site.block",array(
			"mode" => "block",
			"moduleId" => $this->getModuleId(),
			"config" => $this->getConfig()
		));
		
		if($this->delegator->invokeExecute($blockComponent,$htmlObj)){
			return parent::execute($blockComponent,$htmlObj);	
		}
		
		return;	
	}
	
	function onSave(){
		
		if(isset($_POST["change-script"]) && isset($_POST["block_script_id"])){
			$this->setModuleId($_POST["block_script_id"]);
		}
		
	}
	
	
	/**
	 * 公開側で使用
	 */
	function getEntries(){
		
		if($this->delegator){
			return $this->delegator->getEntries($this->countFrom,$this->countTo);
		}
		
		
		return array();
	}
	
	function onCreate(){
		
	}
	
	function onDelete(){
		
	}
	
	/* properties */
	
	private $moduleId;	//module id
	private $countFrom;
	private $countTo;
	
	private $config = array();
	
	/* getter setter */

	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getCountFrom() {
		return $this->countFrom;
	}
	function setCountFrom($countFrom) {
		$this->countFrom = $countFrom;
	}

	function getCountTo() {
		return $this->countTo;
	}
	function setCountTo($countTo) {
		$this->countTo = $countTo;
	}
}


?>