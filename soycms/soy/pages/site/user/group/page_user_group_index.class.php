<?php
/**
 * グループ一覧
 */
class page_user_group_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
	}

	function page_user_group_index() {
		WebPage::WebPage();
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		$this->createAdd("group_list","_class.list.GroupList");
		
	}
}
?>