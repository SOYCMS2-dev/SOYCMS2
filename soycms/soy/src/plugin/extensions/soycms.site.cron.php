<?php
/*
 * クロンで実行する
 */
class SOYCMS_SiteCronAction implements SOY2PluginAction{
	
	function execute(){
		
	}
	
	function daily(){
		
	}
	
}
class SOYCMS_SiteCronActionDelegater implements SOY2PluginDelegateAction{
	
	private $mode = "cron";

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		___log("run module:" . $moduleId);
		
		if($this->mode == "daily"){
			$action->daily();
		}else{
			$action->execute();
		}
		___log("finish run module:" . $moduleId);
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
PluginManager::registerExtension("soycms.site.cron","SOYCMS_SiteCronActionDelegater");
