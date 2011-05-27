<?php

class ScriptBlockComponentFormPage extends HTMLPage{
	
	private $obj;
	
	function ScriptBlockComponentFormPage($obj){
		$this->obj = $obj;
		HTMLPage::HTMLPage();
	}
	
	function main(){
		PluginManager::load("soycms.site.block");
		
		$delegetor = PluginManager::invoke("soycms.site.block",array("mode" => "list")); 
		
		//script select
		$this->addSelect("script_select",array(
			"name" => "block_script_id",
			"options" => $delegetor->getList(),
			"selected" => $this->obj->getModuleId()
		));
		
		$this->addLabel("script_config",array(
			"visible" => $this->obj->getModuleId(),
			"html" => ($this->obj->getModuleId()) ? 
				PluginManager::display("soycms.site.block",array("mode" => "form","moduleId" => $this->obj->getModuleId(), "config" => $this->obj->getConfig()))
				: ""
		));
		
		$this->addInput("countFrom",array(
			"name" => "object[countFrom]",
			"value" => $this->obj->getCountFrom()
		));
		
		$this->addInput("countTo",array(
			"name" => "object[countTo]",
			"value" => $this->obj->getCountTo()
		));
		
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
	
	
}
?>