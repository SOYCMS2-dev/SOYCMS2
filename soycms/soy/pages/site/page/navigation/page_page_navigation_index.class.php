<?php
/**
 * @title ナビゲーション管理
 */
class page_page_navigation_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["NavigationOrder"])){
			$orders = $_POST["NavigationOrder"];
			$orders = array_keys($orders);
			SOYCMS_DataSets::put("navigation.order",$orders);
		}
		
		$this->jump("/page/navigation");
	}

	function page_page_navigation_index(){
		WebPage::WebPage();
		
		$this->addForm("form");
		$this->createAdd("navigation_list","_class.list.NavigationList");
		
	}
}