<?php

class page_ext_api extends SOYCMS_WebPageBase{
	
	private $id;
	
	function init(){
		PluginManager::load("soycms.site.ajax",$this->id);
	}
	
	function page_ext_api($args) {
		$this->id = $args[0];
		PluginManager::invoke("soycms.site.ajax");
	}
}
?>