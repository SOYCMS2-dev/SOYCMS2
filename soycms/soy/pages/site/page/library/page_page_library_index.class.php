<?php
/**
 * @title ライブラリ管理
 */
class page_page_library_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["LibraryOrder"])){
			$orders = $_POST["LibraryOrder"];
			$orders = array_keys($orders);
			SOYCMS_DataSets::put("library.order",$orders);
		}
		
		$this->jump("/page/library");
	}

	function page_page_library_index(){
		WebPage::WebPage();
		
		$this->addForm("form");
		$this->createAdd("library_list","_class.list.LibraryList");
	}
}