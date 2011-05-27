<?php
/**
 * @title ライブラリの作成
 */
class page_page_snippet_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Snippet"])){
			SOY2::cast($this->snippet,(object)$_POST["Snippet"]);
			$this->snippet->save();
		}
		
		$this->jump("/page/snippet/detail?id=".$this->snippet->getId()."&updated");
	}
	
	private $id;
	private $snippet;
	private $parentSnippet;
	
	function init(){
		$this->id = $_GET["id"];
		$this->snippet = SOYCMS_Snippet::load($this->id);
		if(!$this->snippet){
			$this->jump("/page/snippet");
		}
		if($this->snippet->getGroup()){
			$this->parentSnippet = SOYCMS_Snippet::load($this->snippet->getGroup());
		}
	}

	function page_page_snippet_detail(){
		WebPage::WebPage();
		
		$this->addLabel("parent_snippet_name_text",array(
			"text" => ($this->parentSnippet) ? $this->parentSnippet->getName() : "",
		));
		
		$this->addModel("is_child_snippet",array(
			"visible" => ($this->parentSnippet)
		));
		
		$this->addLabel("snippet_class",array(
			"text" => ($this->parentSnippet) ? $this->parentSnippet->getClass() : "",
		));
		
		$this->createAdd("form","_class.form.SnippetForm",array(
			"snippet" => $this->snippet
		));
		
		$this->addModel("is_not_child_snippet",array(
			"visible" => (!$this->parentSnippet)
		));
		
	}
}