<?php

class page_page_template_items extends SOYCMS_WebPageBase{
	
	private $pageId;
	private $navigationId;
	private $templateId;
	private $type = "library";
	private $page = 1;
	private $limit = 5;
	private $max;

	function page_page_template_items() {
		if(isset($_GET["type"]))$this->type = $_GET["type"];
		if(isset($_GET["template"]))$this->templateId = $_GET["template"];
		if(isset($_GET["navigation"]))$this->navigationId = $_GET["navigation"];
		if(isset($_GET["page"]))$this->page = $_GET["page"];
		WebPage::WebPage();
		
		$items = $this->getItems($this->type);
		
		$this->createAdd("item_list","TemplateItems_ItemList",array(
			"list" => $items
		));
		
		foreach(array("library","navigation","block") as $key){
			$this->addModel("mode_{$key}",array("visible"=>$this->type == $key));
		}
		
		//ページャ
		
		$this->addModel("library_pager",array(
			"visible" => $this->type == "library"
		));
		
		$max = ceil($this->max / $this->limit);
		$this->addLabel("library_pager_max",array("text" => $max));
		$this->addSelect("library_pager_select",array(
			"options" => range(1,$max),
			"selected" => $this->page
		));
		$this->addLink("library_pager_prev",array(
			"link" => "#" . ($this->page - 1),
			"visible" => ($this->page > 1)
		));
		$this->addLink("library_pager_next",array(
			"link" => "#" . ($this->page + 1),
			"visible" => ($this->page < $max)
		));
	
	}
	
	function getItems($type){
		$result = array();
		
		switch($type){
			case "block":
				try{
					if($this->navigationId){
						$obj = SOYCMS_Navigation::load($this->navigationId);
						$class = "SOYCMS_NavigationItem";
					}else{
						$obj = SOYCMS_Template::load($this->templateId);
						$class = ($this->pageId) ? "SOYCMS_HTMLItem" : "SOYCMS_TemplateItem";
					}
					
					$blocks = $obj->getBlocks();
					$defaults = $obj->getDefaultBlocks();
					$items = $obj->getItems();
					
					foreach($items as $_key => $_item){
						$key = str_replace("block:","",$_key);
						if(isset($blocks[$key]))unset($blocks[$key]);
						if(isset($defaults[$_key]))unset($defaults[$_key]);
					}
					
					$tmp = array();
					foreach($blocks as $key => $block){
						$obj = new $class();
						
						$obj->setId($block->getId());
						$obj->setType($type);
						$obj->setName($block->getName());
						$obj->setComment($block->getDescription());
						
						if($this->templateId && method_exists($obj,"setTemplateId")){
							$obj->setTemplateId($this->templateId);
						}
						if($this->navigationId)$obj->setNavigationId($this->navigationId);
						
						$tmp["block:" . $key] = $obj;
					}
					
					foreach($defaults as $key => $_item){
						$tmp[$key] = $_item;
					}
					
					return $tmp;
					
				}catch(Exception $e){
					
				}
				
				return array();
				break;
			case "library":
				$libraries = SOYCMS_Library::getList();
				
				$from = ($this->page - 1) * $this->limit;
				$to = ($this->page) * $this->limit + 1;
				$counter = 0;
				
				$this->max = count($libraries);
				
				foreach($libraries as $library){
					
					if($counter < $from){
						$counter++;
						continue;
					}
					if($counter > $to)break;
					
					$obj = new SOYCMS_HTMLItem();
					$obj->setId($library->getId());
					$obj->setType($type);
					$obj->setName($library->getName());
					$obj->setComment($library->getDescription());
					
					$result["library:" . $obj->getId()] = $obj;
					$counter++;		
				}
				
				break;
			case "navigation":
			default:
				$navigations = SOYCMS_Navigation::getList();
				
				foreach($navigations as $library){
					
					$obj = new SOYCMS_HTMLItem();
					$obj->setId($library->getId());
					$obj->setType($type);
					$obj->setName($library->getName());
					$obj->setComment($library->getDescription());
					
					$result["navigation:" . $obj->getId()] = $obj;		
				}
				
				break;
		}
		
		return $result;
	}
	
	
	
	function getLayout(){
		return "blank.php";
	}
}

class TemplateItems_ItemList extends HTMLList{
	
	function populateItem($entity,$key){
		
		$className = "";
		if($entity->getType() == "library"){
			$className = "boxcolor3";
		}else if($entity->getType() == "navigation"){
			$className = "boxcolor1";
		}else if($entity->getType() == "block"){
			$className = "boxcolor2";
		}else if($entity->getType() == "default"){
			$className = "boxcolor4";
		}
		
		$this->addModel("item_box",array(
			"attr:class" => "item_box " . $className,
		));
		
		$this->addLabel("item_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("item_type_text",array(
			"text" => $entity->getTypeText()
		));
		
		$this->addLabel("item_description",array(
			"html" => $entity->getComment()
		));
		
		$link = $entity->getConfigLink();;
		$this->addLink("detail_link",array(
			"link" => $link,
			"visible" => (strlen($link)>0)
		));
		
		$link = $entity->getCopyLink();;
		$this->addLink("copy_link",array(
			"link" => $link,
			"visible" => (strlen($link)>0)
		));
		
		$this->addInput("item_order",array(
			"_name" => "LayoutOrder[" . $entity->getType() . ":" . $entity->getId() . "]",
			"value" => 0
		));
		
		$this->addInput("newitem_id",array(
			"attr:_name" => "NewItem[]",
			"value" => $key
		));	
	}
	
}
?>