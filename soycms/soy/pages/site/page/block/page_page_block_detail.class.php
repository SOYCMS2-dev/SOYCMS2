<?php

class page_page_block_detail extends SOYCMS_WebPageBase{
	
	private $block;
	private $template;
	private $page;
	private $navigation;
	
	function doPost(){
		
		if(isset($_POST["Block"])){
			SOY2::cast($this->block,(object)$_POST["Block"]);
			
			if(isset($_POST["object"])){
				$this->block->setObject($_POST["object"]);
			}
			
			$this->block->save();
		}
		
		if($this->page){
			$this->jump("/page/block/detail?updated&page=" . $this->page->getId() . "&id=" . $this->block->getId());
		}else if($this->template){
			$this->jump("/page/block/detail?updated&template=" . $this->template->getId() . "&id=" . $this->block->getId());
		}else if($this->navigation){
			$this->jump("/page/block/detail?updated&navigation=" . $this->navigation->getId() . "&id=" . $this->block->getId());
		}else{
			$path = str_replace(SOYCMS_SITE_DIRECTORY,"",$this->block->getPath());
			$this->jump("/page/block/detail","?updated&path=" . $path . "");
		}
	}
	
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
				
				
				if(isset($_GET["reset"])){
					$this->block->delete();
					$this->jump("/page/block/detail?page=".$this->page->getId()."&id=" . $this->block->getId());
				}
			}
		}
		
		if(isset($_GET["path"]) && !$this->block){
			$blockDir = SOYCMS_SITE_DIRECTORY . dirname($_GET["path"]) . "/";
			$blockId  = str_replace(".block","",basename($_GET["path"]));
			
			$this->block = SOYCMS_Block::load($blockId,$blockDir);
			
			//Path指定の時にNavigationが設定されるようにする
			$array = explode("/",$_GET["path"]);
			switch(@$array[0]){
				case ".navigation":
					$this->navigation = SOYCMS_Navigation::load(@$array[1]);
					break;
				default:
					break;
			}
		}
		
		if(!$this->block){
			echo "error";
			exit;
		}
		
	}

	function page_page_block_detail() {
		WebPage::WebPage();
		
		
		$this->createAdd("form","_class.form.BlockForm",array(
			"block" => $this->block
		));
		
		
		$this->buildPage();
		
	}
	
	function buildPage(){
		$title = $this->block->getName();
		$return_link = "";
		$return_text = "";
		
		if($this->template){
			$title = $this->template->getName() . " | " . $title;
			$return_link = soycms_create_link("/page/template/detail?id=" . $this->template->getId()) . "#tpl_item";
			$return_text = $this->template->getName();	
		}
		
		if($this->page){
			$title = $this->page->getName() . " | " . $title;
			$return_link = soycms_create_link("/page/detail/" . $this->page->getId()) . "#tpl_config";
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
		
		
		//reset link and return link wrapper
		$this->addModel("is_admin_mode",array(
			"visible" => (strlen($return_link) > 0 || ($this->page && file_exists($this->block->getPath())))
		));
		
		$this->addLink("return_link",array(
			"link" => $return_link,
			"visible" => strlen($return_link) > 0
		));
		
		$this->addLabel("return_text",array(
			"text" => $return_text
		));
		
		$this->addLink("reset_link",array(
			"link" => ($this->page) ? soycms_create_link("/page/block/detail?reset&page=" . $this->page->getId() . "&id=" . $this->block->getId()) : "",
			"visible" => ($this->page && file_exists($this->block->getPath()))
		));
		$this->addModel("reset_link_wrap",array(
			"visible" => ($this->page && file_exists($this->block->getPath()))
		));
		
		$this->addLink("template_block_link",array(
			"link" => ($this->page) ? soycms_create_link("/page/block/detail?template=" . $this->page->getTemplate() . "&id=" . $this->block->getId()) : "" 
		));
		
		
		$this->addModel("mode_page",array(
			"visible" => $this->page
		));
		$this->addLabel("page_name",array(
			"text" => ($this->page) ? $this->page->getName() : ""
		));
		$this->addLink("page_link",array(
			"link" => ($this->page) ? soycms_create_link("/page/detail/" . $this->page->getId()) . "#tpl_config" : ""
		));
		$this->addModel("is_page_block",array(
			"visible" => $this->page && file_exists($this->block->getPath())
		));
		
		$this->addModel("mode_navigation",array(
			"visible" => $this->navigation
		));
		$this->addLabel("navigation_name",array(
			"text" => ($this->navigation) ? $this->navigation->getName() : ""
		));
		$this->addLink("navigation_link",array(
			"link" => ($this->navigation) ? soycms_create_link("/page/navigation/detail?id=" . $this->navigation->getId()) : ""
		));
		
		$this->addModel("mode_template",array(
			"visible" => (!$this->page && $this->template)
		));
		$this->addLabel("template_name",array(
			"text" => ($this->template) ? $this->template->getName() : ""
		));
		$this->addLink("template_link",array(
			"link" => ($this->template) ? soycms_create_link("/page/template/detail?id=" . $this->template->getId()) . "#tpl_item" : ""
		));
		
		$this->addLabel("block_id_crumbs",array(
			"text" => $this->block->getId()
		));
		$this->addLabel("block_name",array(
			"text" => $this->block->getName()
		));
		
		
		/* ブロックのサンプルコード */
		$suffix = "";
		if($this->page)$suffix = "page=" . $this->page->getId();
		if($this->template && !$this->page)$suffix = "template=" . $this->template->getId();
		if($this->navigation)$suffix = "navigation=" . $this->navigation->getId();
		$this->addLink("block_code_link",array(
			"link" => soycms_create_link("/page/block/code") . "?id=" . $this->block->getId() . "&" . $suffix
		));
		
		$templateEditLink = null;
		switch(true){
			case (false == is_null($this->navigation)):
				$templateEditLink = soycms_create_link("page/navigation/detail?id=" . $this->navigation->getId());
				$templateEditLink .= "#navi_template";
				break;
			case (false == is_null($this->template)):
				$templateEditLink = soycms_create_link("page/template/detail?id=" . $this->template->getId());
				$templateEditLink .= "#tpl_config";
				break;
			case (false == is_null($this->page)):
				$templateEditLink = soycms_create_link("page/detail/" . $this->page->getId());
				$templateEditLink .= "#tab3";
				break;
		}
		
		//ブロックのコードの先頭までジャンプさせる
		$templateEditLink .= "/block:" . $this->block->getId();
		
		$this->addLink("template_edit_link",array(
			"link" => $templateEditLink
		));
	}
	
}
?>