<?php
/**
 * @title ライブラリの作成
 */
class page_page_library_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Library"])){
			SOY2::cast($this->library,(object)$_POST["Library"]);
			$this->library->save();
			
			SOYCMS_History::addHistory("library",$this->library);
			
		}
		
		if(isset($_GET["page"]) && is_numeric($_GET["page"])){
			$this->jump("/page/detail/" . (int)$_GET["page"] . "#tpl_config");
		}
		
		
		$this->jump("/page/library/detail?id=" . $this->id . "&updated");
	}
	
	private $id;
	private $library;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->library = SOYCMS_Library::load($this->id);
		if(!$this->library){
			$this->jump("/page/library");
		}
		
		parent::prepare();
	}

	function page_page_library_detail(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.LibraryForm",array(
			"library" => $this->library
		));
		
		$this->addLink("copy_link",array(
			"link" => soycms_create_link("page/library/create?id=") . $this->id
		));
		
		$this->createAdd("history_index","page.history.page_page_history_index",array(
			"type" => "library",
			"name" => $this->library->getName(),
			"objectId" => $this->id
		));
		
	}
}