<?php

class page_config_module extends SOYCMS_WebPageBase{
	
	function doPost(){
		$this->module->postConfigure($this);
		$this->jump("config/module/",$this->id."?updated");
		
	}
	
	
	private $id;
	private $module;
	
	function init(){
		PluginManager::load("plus.user.module",$this->id);
		$this->module = PluginManager::invoke("plus.user.module",array(
			"mode" => "load",
			"moduleId" => $this->id
		))->getModule();
		
		if(is_null($this->module)){
			echo "error";
			exit;
		}
		
		$this->module->setModuleId($this->id);
	}
	
	

	function page_config_module($args) {
		$this->id = @$args[0];
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		$this->addLabel("module_name",array(
			"text" => $this->module->getName()
		));
		
		$this->addLabel("module_form",array(
			"html" => $this->module->getConfigure($this)
		));
	}
	
	function buildForm(){
		
	}
}
?>