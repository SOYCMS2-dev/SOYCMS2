<?php
class SOYCMS_SiteControllerExtension implements SOY2PluginAction{
	
	//initial
	function initialize(){
		
	}
	
	
	//prepare
	function prepare(){
		
	}
	
	
	//end
	function tearDown(){
		
	}
	
	
	function error($e){
		
	}
	

}

class SOYCMS_SiteControllerDelegateAction implements SOY2PluginDelegateAction{
	
	private $exception = null;

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($extensionId == "soycms.site.controller.initialize"){
			$action->initialize();
			return;
		}
		
		if($extensionId == "soycms.site.controller.prepare"){
			$action->prepare();
			return;
		}
		
		if($extensionId == "soycms.site.controller.error"){
			$action->error($this->execption);
			return;
		}
		
		if($extensionId == "soycms.site.controller.teardown"){
			$action->tearDown();
			return;
		}
		
	}


	function getException() {
		return $this->exception;
	}
	function setException($exception) {
		$this->exception = $exception;
	}
}

PluginManager::registerExtension("soycms.site.controller.prepare","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.error","SOYCMS_SiteControllerDelegateAction");
PluginManager::registerExtension("soycms.site.controller.teardown","SOYCMS_SiteControllerDelegateAction");
