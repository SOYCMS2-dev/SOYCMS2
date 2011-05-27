<?php
/**
 * @title 記事の出力
 */
class page_entry_import_index extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;
	
	function doPost(){
		
		if(isset($_FILES["import_file"]) && $_FILES["import_file"]["size"]){
			$type = $_POST["type"];
			$file = $_FILES["import_file"];
			
			$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntryImportExportLogic",array(
				"id" => $this->id,
				"page" => $this->page,
				"type" => $type,
				"options" => (isset($_POST["options"]) && is_array($_POST["options"])) ? $_POST["options"] : array()
			));
			
			$logic->prepare();
			$count = $logic->importToDirectory(file_get_contents($file["tmp_name"]));
			
			$this->jump("/entry/import/" . $this->id . "?imported=" . implode(",",$count));
		}
		
	}
	
	function init(){
		$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
	}
	
	function page_entry_import_index($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->addUploadForm("form");
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildForm(){
		
		/* オプション */
		
		$this->addCheckbox("import_append",array(
			"elementId" => "import_append",
			"name" => "options[append]",
			"value" => 1,
			"isBoolean" => true,
		));
		
		$this->addCheckbox("import_draft",array(
			"elementId" => "import_draft",
			"name" => "options[draft]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => true
		));
		
		/* 種別 */
		
		$this->addCheckbox("import_type_soycms2",array(
			"elementId" => "import_type_soycms2",
			"name" => "type",
			"value" => "soycms2",
			"selected" => true
		));
		
		$this->addCheckbox("import_type_csv",array(
			"elementId" => "import_type_csv",
			"name" => "type",
			"value" => "csv",
		));
		
		$this->addCheckbox("import_type_mt",array(
			"elementId" => "import_type_mt",
			"name" => "type",
			"value" => "mt",
		));
		
		$this->addCheckbox("import_type_epub",array(
			"elementId" => "import_type_epub",
			"name" => "type",
			"value" => "epub",
		));
		
			
	}
	
	function buildPage(){
		$this->addLabel("page_name",array(
			"text" => $this->page->getName()
		));
		
		$this->addForm("mode_result",array(
			"visible" => (isset($_GET["imported"]))
		));
		
		list($new,$update) = (isset($_GET["imported"])) ? explode(",",$_GET["imported"]) : array(0,0);
		
		$this->addLabel("import_count",array( "text" => ($new + $update)));
		$this->addLabel("import_new",array( "text" => (int)($new)));
		$this->addLabel("import_update",array( "text" => (int)($update)));
		
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