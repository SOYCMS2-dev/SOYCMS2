<?php

class page_page_item_config extends SOYCMS_WebPageBase{
	
	private $id;
	private $type;
	private $pageId;
	
	private $page;
	private $template;
	
	private $item;
	private $config;
	
	private $itemName;
	
	function init(){
		try{
			$this->id = $_GET["id"];
			$this->type = $_GET["type"];
			$this->pageId = $_GET["pageId"];
			$this->page = SOY2DAO::find("SOYCMS_Page",$this->pageId);
			$this->template = SOYCMS_Template::load($this->page->getTemplate());
			
			//load
			switch($this->type){
				case "block":
					$this->item = SOYCMS_PageItem::getBlock($this->page,$this->id);
					$this->itemName = $this->item->getName();
					break;
				case "library":
					$this->item = SOYCMS_Library::load($this->id);
					$this->itemName = $this->item->getName();
					break;
				case "navigation":
					$this->item = SOYCMS_Navigation::load($this->id);
					$this->itemName = $this->item->getName();
					break;
				case "default":
					$class = $this->page->getPageObjectClassName();
					$blocks = array();
					eval('$blocks = $class::getDefaultBlocks();');
					$this->itemName = @$blocks[$this->id];
					break;
			}
			
			//load config
			$this->config = SOYCMS_PageItem::loadItemConfig($this->page);
			
		}catch(Exception $e){
			//
			var_dump($e);
			exit;
		}
	}

	function page_page_item_config() {
		WebPage::WebPage();
		
		$this->buildPages();
		$this->buildForm();
	}
	
	function buildPages(){
		$this->addLabel("template_name",array(
			"text" => $this->template->getName()
		));
		$this->addLabel("page_name",array(
			"text" => $this->page->getName()
		));
		$this->addLabel("item_name",array(
			"text" => $this->itemName
		));
		$this->addLabel("item_type",array(
			"text" => ucfirst($this->type)
		));
		
		$this->addLink("template_link",array(
			"link" => soycms_create_link("/page/template/detail/?id=") . $this->template->getId()
		));
		$this->addLink("page_link",array(
			"link" => soycms_create_link("/page/detail/" . $this->page->getId())
		));
		$this->addLink("page_item_link",array(
			"link" => soycms_create_link("/page/detail/" . $this->page->getId()) . "#tpl_config"
		));
	}
	
	function buildForm(){
		$this->addForm("form");
		$config = (isset($this->config[$this->id])) ? $this->config[$this->id]
			 : SOYCMS_PageItemConfig::init($this->page,$this->id);
		
		$options = $config->getOptions();
		$_options = array(
			"after_second_page" => false,
			"first_page" => false,
			"before_last_page" => false,
			"last_page" => false,
			"select_label" => false,
			"no_label" => false
		);
		
		foreach($_options as $key => $value){
			$this->addCheckbox("option_hide_" . $key,array(
				"name" => "Config[options][{$key}]",
				"value" => 1,
				"isBoolean" => true,
				"selected" => @$options[$key]
			));
		}
		
		$this->createAdd("rule_list","ItemRuleList",array(
			"list" => $config->getRules()
		));
	}
}

class ItemRuleList extends HTMLList{
	
	function populateItem($entity,$key){
		
		$this->addCheckbox("rule_active",array(
			"name" => "Config[rules][{$key}][active]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $entity["active"]
		));
		
		$this->addInput("rule_url",array(
			"name"=> "Config[rules][{$key}][rule]",
			"value" => $entity["rule"]
		));
		
		$this->addSelect("rule_type",array(
			"name"=> "Config[rules][{$key}][type]",
			"options" => array(
				"visible" => "表示する",
				"hide" => "隠す"
			),
			"selected" => $entity["type"]
		));
		
	}
	
}
?>