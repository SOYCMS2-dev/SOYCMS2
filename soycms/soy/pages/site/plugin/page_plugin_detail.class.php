<?php
/**
 * @title プラグイン一覧
 */
class page_plugin_detail extends SOYCMS_WebPageBase{
	
	private $info;
	
	function init(){
		$id = $_GET["id"];
		$this->info = PluginManager::getPluginInfo($id);
		
		if(isset($_GET["toggle"])){
			$dir = SOYCMS_SITE_DIRECTORY . ".plugin/";
			$this->info->toggleActive($dir);
			
			$this->jump("/plugin/detail?id=" . $id . "&updated");
		}
		
		
	}

	function page_plugin_detail(){
		
		WebPage::WebPage();
		
		$this->buildPage();
		
	}
	
	function buildPage(){
		$this->addLabel("plugin_name",array(
			"text" => $this->info->getName()
		));
		
		
		$this->addLabel("plugin_description",array(
			"html" => nl2br(htmlspecialchars($this->info->getDescription()))
		));
		
		$this->addLink("active_link",array(
			"link" => "?toggle&id=" . $this->info->getId(),
			"visible" => !$this->info->isActive()
		));
		
		$this->addLink("remove_link",array(
			"link" => "?toggle&id=" . $this->info->getId(),
			"visible" => $this->info->isActive()
		));
	}
}