<?php

class page_page_block_create extends SOYCMS_WebPageBase{
	
	private $block;
	private $template;
	private $page;
	private $navigation;
	
	function doPost(){
		
		if(isset($_POST["Block"])){
			$obj = $this->block->getObject();
			SOY2::cast($this->block,(object)$_POST["Block"]);
			
			if(isset($_POST["object"])){
				SOY2::cast($obj,$_POST["object"]);
				$this->block->setObject($obj);
			}
			
			$this->block->save();
		}
		
		
		if($this->page){
			$this->jump("/page/detail/". $this->page->getId() . "#tpl_config");
		}
		if($this->template){
			$this->jump("/page/template/detail?id=". $this->template->getId() . "#tpl_item");
		}
		if($this->navigation){
			$this->jump("/page/navigation/detail?id=". $this->navigation->getId());
		}
	}
	
	function init(){
		
		if(isset($_GET["page"])){
			$this->page = SOY2DAO::find("SOYCMS_Page",$_GET["page"]);
			
			if($this->page){
				$this->template = SOYCMS_Template::load($this->page->getTemplate());
			}
		}
		
		if(isset($_GET["template"])){
			$this->template = SOYCMS_Template::load($_GET["template"]);
		}
		
		if($this->template){
			
			$item = $this->template->getItem("block:" . $_GET["id"]);
			if(!$item){
				$item = new SOYCMS_TemplateItem();
				$item->setId("block:" . $_GET["id"]);
				$item->setName($_GET["id"]);
			}
			
			if($item->generate($this->template,array("type" => $_GET["type"]))){
				$item->prepare();
				$this->block = $item->getObject();
			}
			
		}
		
		
		if(isset($_GET["navigation"])){
			$this->navigation = SOYCMS_Navigation::load($_GET["navigation"]);
			if($this->navigation){
				$item = $this->navigation->getItem("block:" . $_GET["id"]);
				if(!$item){
					$item = new SOYCMS_NavigationItem();
					$item->setId("block:" . $_GET["id"]);
					$item->setName($_GET["id"]);
				}
				
				if($item->generate($this->navigation,array("type" => $_GET["type"]))){
					$item->prepare();
					$this->block = $item->getObject();
				}
			}
		}
		
		if(!$this->block){
			if($this->page){
				$this->jump("/page/detail/". $this->page->getId() . "#tab2");
			}
			if($this->template){
				$this->jump("/page/template/detail?id=". $this->template->getId() . "#tpl_item");
			}
			if($this->navigation){
				$this->jump("/page/navigation/detail?id=". $this->navigation->getId());
			}
			exit;
		}
		
	}

	function page_page_block_create() {
		WebPage::WebPage();
		
		$this->buildPage();
		
		$this->createAdd("form","_class.form.BlockForm",array(
			"block" => $this->block
		));
	}
	
	function buildPage(){
		$title = $this->block->getName();
		$return_link = "";
		$return_text = "";
		
		if($this->template){
			$title = $this->template->getName() . " | " . $title;
			$return_link = soycms_create_link("/page/template/detail?id=" . $this->template->getId());
			$return_text = $this->template->getName();	
		}
		
		if($this->page){
			$title = $this->page->getName() . " | " . $title;
			$return_link = soycms_create_link("/page/detail/" . $this->page->getId());
			$return_text = $this->page->getName();	
		}
		
		if($this->navigation){
			$title = $this->navigation->getName() . " | " . $title;
			$return_link = soycms_create_link("/page/navigation/detail?id=" . $this->navigation->getId());
			$return_text = $this->navigation->getName();
		}
		
		$this->addLabel("window_title",array(
			"text" => $title
		));
		
		$this->addLink("return_link",array(
			"link" => $return_link,
		));
		
		$this->addLabel("return_text",array(
			"text" => $return_text
		));
		
		$this->addLink("reset_link",array(
			"link" => ($this->page) ? soycms_create_link("/page/block/detail?reset&page=" . $this->page->getId() . "&id=" . $this->block->getId()) : "",
			"visible" => ($this->page && file_exists($this->block->getPath()))
		));
		
		$this->addLink("template_block_link",array(
			"link" => ($this->page) ? soycms_create_link("/page/block/detail?template=" . $this->page->getTemplate() . "&id=" . $this->block->getId()) : "" 
		));
		
		$this->addModel("mode_page",array(
			"visible" => ($this->page && !file_exists($this->block->getPath()))
		));
	}
	
}
?>