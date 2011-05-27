<?php

class PluginList extends HTMLList{
	
	private $detailLink;
	
	function init(){
		$this->detailLink = soycms_create_link("/plugin/detail");
	}

	function populateItem($entity){
		$entity->prepare();
		
		$this->addLabel("plugin_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("plugin_description",array(
			"text" => $entity->getDescription(),
			"width" => 120
		));
		
		$this->addLink("detail_link",array(
			"link" => $this->detailLink . "?id=" . $entity->getId()
		));
		
		$this->addLabel("status_text",array(
			"html" => ($entity->isActive()) ? "有効" : "<strong>無効</strong>"
		));	
	}
}
?>