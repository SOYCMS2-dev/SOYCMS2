<?php

class LibraryForm extends HTMLForm{
	
	private $library;
	
	function init(){
		$library = $this->getLibrary();
		
		$this->_soy2_parent->addLabel("library_name_text",array(
			"text" => $library->getName()
		));
	}

	function execute() {
		$this->buildForm($this->getLibrary());
		parent::execute();
		
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($library){
		
		$this->addLabel("library_id_text",array(
			"text" => $library->getId()
		));
		
		$this->addInput("library_id",array(
			"name" => "Library[id]",
			"value" => $library->getId()
		));
		
		//ライブラリ名
		$this->addInput("library_name",array(
			"name" => "Library[name]",
			"value" => $library->getName()
		));
		
		//説明
		$this->addTextArea("library_description",array(
			"name" => "Library[description]",
			"text" => $library->getDescription()
		));
		
		
		//テンプレート本体
		$this->addTextArea("library_content",array(
			"name" => "Library[content]",
			"text" => $library->getContent()
		));
		$this->addTextArea("library_content_area",array(
			"name" => "Library[content]",
			"text" => $library->getContent()
		));
		
		$this->addLabel("hoge",array("text"=>date("Ymd")));
		
		
	}

	function getLibrary() {
		return $this->library;
	}
	function setLibrary($library) {
		$this->library = $library;
	}
}

?>