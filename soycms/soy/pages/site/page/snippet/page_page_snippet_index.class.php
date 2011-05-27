<?php
/**
 * @title ライブラリ管理
 */
class page_page_snippet_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["SnippetOrder"])){
			$orders = $_POST["SnippetOrder"];
			$orders = array_keys($orders);
			SOYCMS_DataSets::put("snippet.order",$orders);
			
			$this->jump("/page/snippet?updated");
		}
		
		if(isset($_POST["save_wysiwyg_config"])){
			SOYCMS_DataSets::put("editor_custom_css",$_POST["Data"]["editor_custom_css"]);
		
			$this->jump("/page/snippet#wysiwyg_config");
		}
		
		$this->jump("/page/snippet");
	}

	function page_page_snippet_index(){
		WebPage::WebPage();
		
		$this->addForm("form");
		$this->createAdd("snippet_list","_class.list.SnippetList");
		
		
		$this->addForm("config_form");
		
		$this->addTextArea("editor_custom_css",array(
			"name" => "Data[editor_custom_css]",
			"value" => SOYCMS_DataSets::get("editor_custom_css","")
		));

	}
}