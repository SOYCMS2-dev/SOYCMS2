<?php
/**
 * @title 管理者一覧
 */
class page_user_index extends SOYCMS_WebPageBase{

	function page_user_index(){
		WebPage::WebPage();
		
		$userDAO = SOY2DAOFactory::create("SOYCMS_UserDAO");
		$users = $userDAO->get();
		
		$this->createAdd("user_list","_class.list.UserList",array(
			"list" => $users
		));
	}
}