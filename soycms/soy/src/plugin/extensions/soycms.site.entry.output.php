<?php

class SOYCMS_EntryOutputActionBase implements SOY2PluginAction{
	
	function onOutput(SOYCMS_Entry $entry){
		
	}

}
class SOYCMS_EntryOutputDelegateAction implements SOY2PluginDelegateAction{
	
	private $entry;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		$action->onOutput($this->entry);
		
	}
	
	function getContent($values){
		if($this->action){
			return $this->action->getContent($values);
		}
		return "";
	}
	
	/* getter setter */


	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
}
PluginManager::registerExtension("soycms.site.entry.output.detail","SOYCMS_EntryOutputDelegateAction");
PluginManager::registerExtension("soycms.site.entry.output.list","SOYCMS_EntryOutputDelegateAction");
?>