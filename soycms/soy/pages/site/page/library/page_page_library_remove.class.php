<?php
/**
 * @title ライブラリの削除
 */
class page_page_library_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			SOYCMS_Library::remove($this->library->getId());
			$this->jump("/page/library?deleted");
		}
		
		
		$this->jump("/page/library");
		
	}
	
	private $id;
	private $library;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->library = SOYCMS_Library::load($this->id);
		
		
		parent::prepare();
	}

	function page_page_library_remove(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("library_name",array(
			"text" => $this->library->getName()
		));
	}
	
	function buildForm(){
		$this->addForm("form");
	}
}
