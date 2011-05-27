<?php
/**
 * @title サイトのデータ管理
 */
class page_manager_index extends SOYCMS_WebPageBase{

	function page_manager_index(){
		WebPage::WebPage();
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		$count = $this->countAll();
		
		foreach($count as $key => $value){
			$this->addLabel("{$key}_count",array(
				"text" => $value
			));	
		}
		
		$this->addUploadForm("contents_form",array(
			"action" => soycms_create_link("manager/contents/import")
		));
		
		$this->addUploadForm("design_form",array(
			"action" => soycms_create_link("manager/design/import")
		));

	}
	
	function countAll(){
		$count = array(
			"contents" => 0,
			"directory" => 0,
			"template" => 0,
			"library" => 0,
			"snippet" => 0,
			"navigation" => 0
		);
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$count["contents"] = $dao->count();
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$count["directory"] = $dao->countByType("detail");
		
		$res = SOYCMS_DataSets::get("template.keys", -1);
		$count["template"] = (is_array($res)) ? count($res) : "取得出来ません";
		
		$res = SOYCMS_DataSets::get("library.keys", -1);
		$count["library"] = (is_array($res)) ? count($res) : "取得出来ません";
		
		$res = SOYCMS_DataSets::get("snippet.keys", -1);
		$count["snippet"] = (is_array($res)) ? count($res) : "取得出来ません";
		
		$res = SOYCMS_DataSets::get("navigation.keys", -1);
		$count["navigation"] = (is_array($res)) ? count($res) : "取得出来ません";
		
		return $count;
	}
	
}