<?php

class page_entry_sections extends SOYCMS_WebPageBase{

	function doPost(){
		
		$res = SOYCMS_EntrySection::unserializeSection($_POST["sections"]);
		
		$this->entry->setSectionsList($res);
		$this->entry->save();
		
		$this->jump("/entry/sections/" . $this->entry->getId() . "?updated");
	}
	
	private $id;
	private $entry;

	function page_entry_sections($args) {
		$this->id = $args[0];
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		
		WebPage::WebPage();
		
		$this->addTextArea("sections",array(
			"name" => "sections",
			"value" => $this->entry->getSections()
		));
		
		$this->addLabel("entry_title",array(
			"text" => $this->entry->getTitle()
		));
		
		$this->addForm("form");
		
	}
	
	function getSubMenu(){
		
		$menu = SOY2HTMLFactory::createInstance("entry.page_entry_detail_submenu",array(
			"arguments" => array($this->id,$this->entry,"sections")
		));
		$menu->display();
	}
}
?>