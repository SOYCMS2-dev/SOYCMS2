<?php

class TemplateComplexTypeList extends HTMLList{
	
	private $link;
	private $suffix;
	
	function populateItem($entity,$key){
		
		$this->addModel("template_complex_type_item",array(
			"attr:class" => ($key == @$_GET["template"]) ? "on" : ""
		));
		
		$this->addLink("template_complex_type_link",array(
			"link" => $this->link . "&template=" . $key . (($this->suffix) ? $this->suffix : ""),
		));
		$this->addLabel("template_complex_type_name",array(
			"text" => $entity
		));
		
	}
	
	function setLink($link){
		$this->link  =$link;
	}
	function setSuffix($suffix){
		$this->suffix = $suffix;
	}
}