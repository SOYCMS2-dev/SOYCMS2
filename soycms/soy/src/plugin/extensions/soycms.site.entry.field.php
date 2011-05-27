<?php

class SOYCMS_EntryCustomFieldBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(SOYCMS_Entry $entry){

	}

	/**
	 * doPost
	 */
	function doPost(SOYCMS_Entry $entry){

	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj,SOYCMS_Entry $entry,$mode = "list"){

	}

	/**
	 * @onDelete
	 */
	function onDelete($id){


	}

}
class SOYCMS_EntryCustomFieldDelegateAction implements SOY2PluginDelegateAction{

	private $mode = "form";	//form,list,detail,update,delete
	private $entry;
	private $htmlObj;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			case "form":
				$entry = $this->getEntry();
				if(!$entry)$entry = new SOYCMS_Entry();
				echo $action->getForm($entry);
				break;
			case "update":
				$action->doPost($this->getEntry());
				break;
			case "delete":
				$action->onDelete($this->getEntry()->getId());
				break;
			case "detail":
			case "list":
			default:
				$action->onOutput($this->htmlObj,$this->getEntry(),$this->mode);
				break;
		}
	}
	
	/* getter setter */

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
	function getHtmlObj() {
		return $this->htmlObj;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
}
PluginManager::registerExtension("soycms.site.entry.field","SOYCMS_EntryCustomFieldDelegateAction");
?>