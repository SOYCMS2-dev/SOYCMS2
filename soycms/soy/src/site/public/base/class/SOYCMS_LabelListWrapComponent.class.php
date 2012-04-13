<?php

class SOYCMS_LabelListWrapComponent extends SOYBodyComponentBase{
	
	private $dirUrl = null;
	private $list = array();
	
	function execute(){

		$this->createAdd("label_list","SOYCMS_LabelListComponent",array(
			"dirUrl" => $this->dirUrl,
			"soy2prefix" => "cms",
			"list" => $this->list,
			"buildEntryList" => false
		));
		
		
		parent::execute();
	}

	/* getter setter */

	function getDirUrl() {
		return $this->dirUrl;
	}
	function setDirUrl($dirUrl) {
		$this->dirUrl = $dirUrl;
	}
	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}
}
?>