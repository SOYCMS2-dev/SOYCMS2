<?php
/**
 * @title ライブラリ作成
 */
class page_page_library_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Library"])){
			SOY2::cast($this->library,(object)$_POST["Library"]);
			
			$tmp = SOYCMS_Library::load($this->library->getId());
			
			if(!$tmp){
				$this->library->save();
				$this->jump("/page/library/detail?id=" . $this->library->getId() . "&created");
			}
		}
		
		$this->error = true;
		
	}
	
	private $library;
	private $error = false;
	
	function prepare(){
		$this->library = new SOYCMS_Library();
		
		if(isset($_GET["id"])){
			$old = SOYCMS_Library::load($_GET["id"]);
			
			if($old){
				$old->setId($_GET["id"] . "_copy");
				$this->library = $old;
			}
		}
		
		parent::prepare();
	}

	function page_page_library_create(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.LibraryForm",array(
			"library" => $this->library
		));
		
		$this->addModel("create_error",array(
			"visible" => $this->error
		));
		
	}
}