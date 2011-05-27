<?php

class SOYCMS_ApplicationPageBase extends SOYCMS_SitePageBase{
	
	function SOYCMS_ApplicationPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}
	
	function doPost(){
		$page = $this->getPageObject();
		$pageObj = $page->getPageObject();
		
		PluginManager::load("soycms.site.application",$pageObj->getApplicationId());
		
		PluginManager::invoke("soycms.site.application",array(
			"htmlObj" => $this,
			"pageObj" => $page,
			"mode" => "post"
		));
		
	}

	function build($args){
		$page = $this->getPageObject();
		$pageObj = $page->getPageObject();
		
		PluginManager::load("soycms.site.application",$pageObj->getApplicationId());
		
		$html = PluginManager::display("soycms.site.application",array(
			"htmlObj" => $this,
			"pageObj" => $page,
			"mode" => "form"
		));
		
		$this->addLabel("app_main",array(
			"html" => $html,
			"soy2prefix" => "block",
			"visible" => $this->isItemVisible("default:app_main")
		));
			
	}

}

?>