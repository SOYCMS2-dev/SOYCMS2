<?php
/**
 * @title ページのコピー
 */
class page_page_copy extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;
	
	function init(){
		try{
			$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
		}catch(Exception $e){
			$this->jump("/page/list");
		}
		
		$this->page->setName("copy of " . $this->page->getName());
		$this->page->setUri($this->page->getUri() . "_copied");
	}
	
	function doPost(){
		$page = $this->page;
		
		SOY2::cast($page,(object)$_POST["Page"]);
		
		if(isset($_POST["uri_prefix"])){
			$page->setUri($_POST["uri_prefix"] . $page->getUri());
		}
		
		if(isset($_POST["duplicate_alias"])){
			$page->setType("alias");
			if(!isset($_POST["object"]) || !is_array($_POST["object"]))$_POST["object"] = array();
			$_POST["object"]["directory"] = $this->id;
		}
		
		$page->setId(null);
		
		if($page->check()){
			/* @var $logic SOYCMS_PageLogic */
			$logic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
			$source = SOY2DAO::find("SOYCMS_Page",$this->id);
			$logic->duplicate($source,$page);
			
			if(isset($_POST["object"])){
				$object = $page->getPageObject();
				SOY2::cast($object,(object)$_POST["object"]);
				$object->save();
			}
			
			if(isset($_POST["duplicate_alias"])){
				$source_index = SOY2DAO::find("SOYCMS_Page",array("uri" => $source->getIndexUri()));
				$dst_index = clone($source_index);
				$dst_index->setId(null);
				$dst_index->setUri($page->getIndexUri());
				if(method_exists($dst_index,"setTargetUri")){
					$dst_index->setTargetUri($page->getUri());
				}
				$logic->duplicate($source_index,$dst_index);
			}
		}
		
		$id = $page->getId();
		
		SOY2PageController::jump("page.list?created=$id");
	}

	function page_page_copy($args){
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->createAdd("dir_form","_class.form.PageForm",array(
			"page" => $this->page
		));
		$this->createAdd("form","_class.form.PageForm",array(
			"page" => $this->page
		));
		
		
		$this->addModel("type_dir",array(
			"visible" => ($this->page->getType() == "detail")
		));
		$this->addModel("type_page",array(
			"visible" => ($this->page->getType() != "detail")
		));
		
		$this->createAdd("directory_tree","_class.list.PageTreeComponent",array(
			"type" => "detail",
			"checkboxName" => "object[directory]",
			"selected" => array(1) 
		));
	}
}