<?php
/**
 * @title テンプレートの削除
 */
class page_page_template_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			SOYCMS_Template::remove($this->template->getId());
		}
		
		$this->jump("/page/template?deleted");
		
	}
	
	private $id;
	private $template;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->template = SOYCMS_Template::load($this->id);
		
		
		parent::prepare();
	}

	function page_page_template_remove(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("template_name",array(
			"text" => $this->template->getName()
		));
	}
	
	function buildForm(){
		$this->addForm("form");
	}
}
