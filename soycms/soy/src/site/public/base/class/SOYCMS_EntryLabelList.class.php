<?php

class SOYCMS_EntryLabelList extends HTMLList{

	private $labels = array();
	private $uri = null;
	
	function populateItem($entity){
		$labelId = $entity->getLabelId();
		$label = (isset($this->labels[$labelId])) ? $this->labels[$labelId] : new SOYCMS_Label();
		
		$this->addLabel("label_name",array(
			"text" => $label->getName(),
			"soy2prefix" => "cms"
		));	
		
		$this->addLink("label_link",array(
			"link" => soycms_get_page_url($this->uri, rawurlencode($label->getAlias())),
			"soy2prefix" => "cms"
		));
	}
	

	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}

	function getUri() {
		return $this->uri;
	}
	function setUri($uri) {
		$this->uri = $uri;
	}
}
?>