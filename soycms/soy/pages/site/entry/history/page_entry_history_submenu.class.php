<?php
SOY2HTMLFactory::importWebPage("entry.page_entry_detail_submenu");

/**
 * @title 記事編集Subemenu
 */
class page_entry_history_submenu extends page_entry_detail_submenu{
	
	private $revision;
	
	function page_entry_history_submenu($args){
		$this->setId(@$args[0]);
		$this->setEntry(@$args[1]);
		$this->revision = $args[2];
		
		HTMLPage::HTMLPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		
		$this->buildHistory(30,$this->revision);
		
		$this->addLink("entry_detail_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->getId())
		));
		
		$this->addLink("preview_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->getId() . "?preview=" . $this->revision)
		));
	}
	
}
?>