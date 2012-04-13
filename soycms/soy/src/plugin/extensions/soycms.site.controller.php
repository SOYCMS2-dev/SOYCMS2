<?php
class SOYCMS_SiteControllerExtension implements SOY2PluginAction{
	
	//initial
	function initialize($controller){
		
	}
	
	
	//prepare
	function prepare($controller){
		
	}
	
	//load
	function load($arguments){
		return true;
	}
	
	
	//end
	function tearDown($controller){
		
	}
	
	
	function error($e,$controller){
		
	}
	

}

class SOYCMS_SiteControllerDelegateAction implements SOY2PluginDelegateAction{
	
	private $exception = null;
	private $controller = null;

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($extensionId == "soycms.site.controller.initialize"){
			$action->initialize($this->controller);
			return;
		}
		
		if($extensionId == "soycms.site.controller.prepare"){
			$action->prepare($this->controller);
			return;
		}
		
		if($extensionId == "soycms.site.controller.load"){
			$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";
			$array = array_values(array_diff(explode("/",$pathInfo),array("")));
			return $action->load($array);
		}
		
		if($extensionId == "soycms.site.controller.error"){
			$action->error($this->execption,$this->controller);
			return;
		}
		
		if($extensionId == "soycms.site.controller.teardown"){
			$action->tearDown($this->controller);
			return;
		}
		
	}


	function getException() {
		return $this->exception;
	}
	function setException($exception) {
		$this->exception = $exception;
	}

	public function getController(){
		return $this->controller;
	}

	public function setController($controller){
		$this->controller = $controller;
		return $this;
	}
}

PluginManager::registerExtension("soycms.site.controller.initialize","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.prepare","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.load","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.error","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.teardown","SOYCMS_SiteControllerDelegateAction");
