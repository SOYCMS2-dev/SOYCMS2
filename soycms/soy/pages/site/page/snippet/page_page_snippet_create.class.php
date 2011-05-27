<?php
/**
 * @title ライブラリの作成
 */
class page_page_snippet_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Snippet"])){
			SOY2::cast($this->snippet,(object)$_POST["Snippet"]);
			
			if($this->parentSnippet){
				$this->snippet->setGroup($this->parentSnippet->getId());
			}
			
			$this->snippet->setId("snippet_" . (1+count(soy2_scandir(SOYCMS_Snippet::getSnippetDirectory()))));
			$this->snippet->save();
		}
		
		$this->jump("/page/snippet?updated");
	}
	
	private $snippet;
	private $parentSnippet;
	
	function init(){
		$this->snippet = new SOYCMS_Snippet();
		if(isset($_GET["id"])){
			$this->parentSnippet = SOYCMS_Snippet::load($_GET["id"]);
		} 
	}

	function page_page_snippet_create(){
		WebPage::WebPage();
		
		$this->addLabel("parent_snippet_name_text",array(
			"text" => ($this->parentSnippet) ? $this->parentSnippet->getName() : "",
		));
		$this->addLabel("parrent_snippet_class_text",array(
			"text" => ($this->parentSnippet) ? $this->parentSnippet->getClass() : "",
		));
		

		
		$this->addModel("is_child_snippet",array(
			"visible" => ($this->parentSnippet)
		));
		$this->addModel("is_not_child_snippet",array(
			"visible" => (!$this->parentSnippet)
		));
		
		
		$this->createAdd("form","_class.form.SnippetForm",array(
			"snippet" => $this->snippet
		));
		
	}
}