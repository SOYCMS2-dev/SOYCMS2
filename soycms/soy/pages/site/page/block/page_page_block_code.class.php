<?php

class page_page_block_code extends SOYCMS_WebPageBase{
	
	private $block;
	private $template;
	private $page;
	private $navigation;
	
	function init(){
		if(isset($_GET["template"])){
			$this->template = SOYCMS_Template::load($_GET["template"]);
			if($this->template){
				$item = $this->template->getItem("block:" . $_GET["id"]);
				
				if(!$item){
					$item = new SOYCMS_TemplateItem();
					$item->setId("block:" . $_GET["id"]);
					$item->setName($_GET["id"]);
					$item->setTemplateId($_GET["template"]);
				}
				$item->prepare();
				$this->block = $item->getObject();
			}
		}
		
		if(isset($_GET["page"])){
			$this->page = SOY2DAO::find("SOYCMS_Page",$_GET["page"]);
			if($this->page){
				$this->block = SOYCMS_PageItem::getBlock($this->page,$_GET["id"]);
				
				if(isset($_GET["reset"])){
					$this->block->delete();
					$this->jump("/page/block/detail?page=".$this->page->getId()."&id=" . $this->block->getId());
				}
				
				$this->template = SOYCMS_Template::load($this->page->getTemplate());
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
					$item->setNavigationId($_GET["navigation"]);
				}
				$item->prepare();
				$this->block = $item->getObject();
			}
		}
		
		if(!$this->block){
			echo "error";
			exit;
		}
		
	}
	
	function page_page_block_code() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		$this->addForm("form");
	}
	
	function buildPage(){
		
		$this->addTextArea("code_area",array(
			"value" => $this->getSampleCode($this->block)
		));

	}
	
	function getSampleCode($block){
		$res = array();
		$res[] = '<!-- block:id="'.$block->getId().'" -->';
		$res[] = $block->getPreview();
		$res[] = '<!-- /block:id="'.$block->getId().'" -->';
		
		
		return implode("\n",$res);
	}
	
	function getLayout(){
		return "layer.php";
	}
}
