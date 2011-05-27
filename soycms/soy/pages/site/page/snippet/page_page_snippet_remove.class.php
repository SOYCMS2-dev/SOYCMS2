<?php
/**
 * @title ライブラリの削除
 */
class page_page_snippet_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			SOYCMS_snippet::remove($this->snippet->getId());
		}
		
		$this->jump("/page/snippet?deleted");
		
	}
	
	private $id;
	private $snippet;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->snippet = SOYCMS_Snippet::load($this->id);
		
		
		parent::prepare();
	}

	function page_page_snippet_remove(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("snippet_name",array(
			"text" => $this->snippet->getName()
		));
	}
	
	function buildForm(){
		$this->addForm("form");
	}
}
