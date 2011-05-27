<?php

class page_entry_template_list extends SOYCMS_WebPageBase{
	
	function init(){
		
	}

	function page_entry_template_list() {
		WebPage::WebPage();
		
		$this->createAdd("template_tree","_class.list.EntryTreeComponent");
		
	}
}
?>