<?php

class SOYCMS_EntryUpdateEventBase implements SOY2PluginAction{

	/**
	 * @onUpdate
	 */
	function onUpdate(SOYCMS_Entry $entry){


	}

}
class SOYCMS_EntryUpdateEventDelegateAction implements SOY2PluginDelegateAction{

	private $entry;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$action->onUpdate($this->getEntry()->getId());
	}
	
	/* getter setter */

	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
}
PluginManager::registerExtension("soycms.site.entry.update","SOYCMS_EntryUpdateEventDelegateAction");
?>