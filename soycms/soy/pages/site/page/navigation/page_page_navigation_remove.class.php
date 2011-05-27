<?php
/**
 * @title テンプレートの削除
 */
class page_page_navigation_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			SOYCMS_Navigation::remove($this->navigation->getId());
		}
		
		$this->jump("/page/navigation?deleted");
		
	}
	
	private $id;
	private $navigation;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->navigation = SOYCMS_Navigation::load($this->id);
		
		
		parent::prepare();
	}

	function page_page_navigation_remove(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("navigation_name",array(
			"text" => $this->navigation->getName()
		));
	}
	
	function buildForm(){
		$this->addForm("form");
	}
}
