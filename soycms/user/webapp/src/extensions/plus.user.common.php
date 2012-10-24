<?php
/*
 * Created on 2011/01/16
 * Block Extensions
 */
class PlusUser_CommonModule implements SOY2PluginAction{
	
	function prepare($controller,$page){
		
	}
	
	function finish($controller,$page,$arguments){
		
	}
	
}
class PlusUser_CommonModuleAction implements SOY2PluginDelegateAction{
	
	private $page = null;
	private $controller = null;
	private $arguments = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		if($extensionId == "plus.user.common.finish"){
			$action->finish($this->controller,$this->page,$this->arguments);
		}else{
			$action->prepare($this->controller,$this->page,$this->arguments);
		}
		
		
	}
	
	/* getter setter */
	
	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getController() {
		return $this->controller;
	}
	function setController($controller) {
		$this->controller = $controller;
	}
	
	function getArguments() {
		return $this->arguments;
	}
	function setArguments($arguments) {
		$this->arguments = $arguments;
	}
}

PluginManager::registerExtension("plus.user.common.prepare","PlusUser_CommonModuleAction");
PluginManager::registerExtension("plus.user.common.finish","PlusUser_CommonModuleAction");
