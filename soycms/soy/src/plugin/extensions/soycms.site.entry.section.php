<?php

class SOYCMS_EntrySectionBase implements SOY2PluginAction{

	/**
	 * 拡張
	 */
	function getName(){
		
	}
	
	/**
	 * 
	 */
	function getContent($argument){
		
	}
	
	/**
	 * 
	 */
	function getOption(){
		return "";
	}

}
class SOYCMS_EntrySectionDelegateAction implements SOY2PluginDelegateAction{
	
	private $module = null;
	private $action;
	private $mode = "list";	//form,list,detail,update,delete
	private $list = array();
	private $content = null;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			case "build":
				if($moduleId == $this->module){
					$this->action = $action;
				}
				break;
			case "list":
			default:
				$this->list[$moduleId] = array(
					"name" => $action->getName(),
					"option" => $action->getOption(),
				);
				break;
		}
		
	}
	
	function getContent($values){
		if($this->action){
			return $this->action->getContent($values);
		}
		return "";
	}
	
	/* getter setter */

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getList(){
		return $this->list;
	}
	

	function getModule() {
		return $this->module;
	}
	function setModule($module) {
		$this->module = $module;
	}
	function setList($list) {
		$this->list = $list;
	}
}
PluginManager::registerExtension("soycms.site.entry.section","SOYCMS_EntrySectionDelegateAction");
?>