<?php
/**
 * @title 記事の出力
 */
class page_entry_export_index extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;
	
	function doPost(){
		
		$type = $_POST["type"];
		$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntryImportExportLogic",array(
			"id" => $this->id,
			"page" => $this->page,
			"type" => $type
		));
		
		$logic->prepare();
		$logic->outputHeader();
		$logic->exportByDirectory();
		
		$this->jump("");
	}
	
	function init(){
		$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
	}
	
	function page_entry_export_index($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->addForm("form");
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildForm(){
		
		$this->addCheckbox("export_type_soycms2",array(
			"elementId" => "export_type_soycms2",
			"name" => "type",
			"value" => "soycms2",
			"selected" => true
		));
		
		$this->addCheckbox("export_type_csv",array(
			"elementId" => "export_type_csv",
			"name" => "type",
			"value" => "csv",
		));
		
		$this->addCheckbox("export_type_mt",array(
			"elementId" => "export_type_mt",
			"name" => "type",
			"value" => "mt",
		));
		
		$this->addCheckbox("export_type_epub",array(
			"elementId" => "export_type_epub",
			"name" => "type",
			"value" => "epub",
		));
		
			
	}
	
	function buildPage(){
		$this->addLabel("page_name",array(
			"text" => $this->page->getName()
		));
		
		//ツリーの表示
		$this->createAdd("page_entry_tree","entry.page_entry_tree");
		$this->addInput("directory_id",array(
			"value" => $this->id
		));
		$this->addLink("return_link",array(
			"link" => soycms_create_link("entry/list/" . $this->id)
		));
	}
}
?>