<?php

class SnippetForm extends HTMLForm{
	
	private $snippet;
	
	function init(){
		$snippet = $this->getSnippet();
		
		$this->_soy2_parent->addLabel("snippet_name_text",array(
			"text" => $snippet->getName()
		));
	}

	function execute() {
		$this->buildForm($this->getSnippet());
		parent::execute();
		
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($snippet){
		
		$this->addLabel("snippet_id_text",array(
			"text" => $snippet->getId()
		));
		
		//ライブラリ名
		$this->addInput("snippet_name",array(
			"name" => "Snippet[name]",
			"value" => $snippet->getName()
		));
		
		//説明
		$this->addTextArea("snippet_description",array(
			"name" => "Snippet[description]",
			"text" => $snippet->getDescription()
		));
		
		
		//テンプレート本体
		$this->addTextArea("snippet_content",array(
			"name" => "Snippet[content]",
			"text" => $snippet->getContent()
		));
		
		//class
		$this->addSelect("snippet_class_select",array(
			"name" => "Snippet[class]",
			"options" => $this->getSnippetClass(),
			"selected" => $snippet->getClass()
		));
		
		$this->addLabel("snippet_class_select_text",array(
			"text" => $snippet->getClass()
		));
		
		
	}
	
	function getSnippetClass(){
		$array = array(
			"btn-text",
			"btn-image",
			"btn-movie",
			"btn-youtube",
			"btn-googlemap",
			"btn-close",
			"btn-document",
			"btn-heading",
			"btn-quotation",
			"btn-source",
			"btn-box",
			"btn-line",
			"btn-nextpage",
			"btn-orderup",
			"btn-orderdown",
			"btn-readmore",
			"btn-list",	
			"btn-table",
			"btn-user1",
			"btn-user2",
			"btn-user3",
			"btn-user4",
			"btn-user5",
			"btn-user6",
			"btn-user7",
			"btn-user8",
			"btn-user9",
			"btn-user10",
		);
		return $array;
	}

	function getSnippet() {
		return $this->snippet;
	}
	function setSnippet($snippet) {
		$this->snippet = $snippet;
	}
}

?>