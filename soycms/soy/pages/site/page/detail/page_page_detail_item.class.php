<?php

/**
 * @title ブロック一覧
 */
class page_page_detail_item extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;

	function page_page_detail_item($args){
		
		$this->id = @$args[0];
		$this->page = @$args[1];
		
		WebPage::WebPage();
			
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		
		$this->addLabel("page_name",array(
			"text" => $this->page->getName()
		));
	}
	
	function buildForm(){
		
		$this->addForm("form");
		
		$pageObject = $this->page->getPageObject();
		$isUseCustomTemplate = $this->page->isUseCustomTemplate();
		
		if($isUseCustomTemplate){
			$items = array();
			$layout = array();
			$template = new SOYCMS_Template();
		}else{
			$template = SOYCMS_Template::load($this->page->getTemplate());
			if(!$template){
				$template = new SOYCMS_Template();
			}
			
			$layout = $template->getLayout();
			$items = $template->getItems();
			$config = $this->page->loadItemConfig();
			
			$tmp = array();
			foreach($items as $key => $value){
				$tmp[$key] = new SOYCMS_PageItem($value);
				$tmp[$key]->setPageId($this->id);
				
				if($tmp[$key]->getType() == "block"){
					$tmp[$key]->loadPageBlockConfig($this->page,$tmp[$key]->getId());
				}
				
				
				
				if(isset($config[$key])){
					$tmp[$key]->setDeleted($config[$key]["hidden"]);
				}
			}
			$items = $tmp;
			
		}
		
		$this->createAdd("template_item_manager","_class.component.TemplateItemComponent",array(
			"items" => $items,
			"layoutArray" => $layout,
			"mode" => "page",
			"pageId" => $this->page->getId(),
			"templateId" => $template->getId()
		));
		
		//配置変更フォーム
		$this->createAdd("position_form","HTMLForm",array(
			"action" => SOY2FancyURIController::createLink("/page/detail/item/" . $this->id)
		));
		
		$templates = SOYCMS_Template::getListByType($this->page->getType());
		$this->addLabel("template_name",array(
			"text" => (isset($templates[$this->page->getTemplate()])) ? $templates[$this->page->getTemplate()]->getName() : ""
		));
		$this->addLink("template_link",array(
			"link" => soycms_create_link("/page/template/detail?id=" . $this->page->getTemplate()),
			"visible" => (isset($templates[$this->page->getTemplate()]))
		));
		$this->addLink("template_item_link",array(
			"link" => soycms_create_link("/page/template/detail?id=" . $this->page->getTemplate()) . "#tpl_item",
			"visible" => (isset($templates[$this->page->getTemplate()]))
		));
		
		//個別設定の時だけ表示
		$this->createAdd("create_form","HTMLForm",array(
			"action" => SOY2FancyURIController::createLink("/page/detail/item/" . $this->id)
		));
		
			$this->addModel("is_use_custom_template",array(
				"visible" => $isUseCustomTemplate
			));
			
			//新しいブロックを追加
			$this->createAdd("new_block_id","HTMLInput",array(
				"name" => "newId",
				"value" => "blockA"
			));
			
			$this->createAdd("new_block_type","HTMLInput",array(
				"name" => "newType",
				"value" => "directory"
			));
		
		$this->addForm("property_form");
	   	
	   	$template = (isset($templates[$this->page->getTemplate()])) ? $templates[$this->page->getTemplate()] : new SOYCMS_Template();
	   	$properties = $template->getProperties();
	   	$_properties = $this->page->getProperties();
	   	foreach($properties as $key => $value){
	   		if(isset($_properties[$key])){
	   			$properties[$key] = $_properties[$key];
	   		}
	   	}
	   	
	   	$this->addModel("property_exists",array("visible"=>count($properties)>0));
	   	$this->createAdd("property_list","HTMLList",array(
   			'populateItem:function($entity,$key)' => '$this->addLabel("property_key",array("text"=>$key));' .
   					'$this->addInput("property_value",array("name"=>"properties[$key]","value"=>$entity));',
   			"list" => $properties
	   	));
		
		
	}
	
	function getLayout(){
		return "blank";
	}
}
?>