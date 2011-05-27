<?php
/**
 * グループ毎にユーザ一覧
 */
class page_user_group_list extends SOYCMS_WebPageBase{
	
	function doPost(){
		
	}

	function page_user_index() {
		WebPage::WebPage();
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		
	}
	
	function getRoles(){
		return SOYCMS_Role::getRoles();
	}
	
	function getUsers(){
		return SOY2DAO::find("SOYCMS_User");
	}
}
?>