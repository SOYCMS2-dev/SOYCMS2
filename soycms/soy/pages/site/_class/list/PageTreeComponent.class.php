<?php
class PageTreeComponent extends HTMLTree{
	
	private $type = null;
	private $detailLink;
	private $entryLink;
	private $pageLink;
	private $checkboxName;
	private $selected;
	
	function init(){
		$this->detailLink = soycms_create_link("/page/detail");
		$this->entryLink = soycms_create_link("/entry");
		$this->pageLink = soycms_create_link("/page/");;
		$this->tree = SOYCMS_DataSets::get("site.page_tree",array());
		if(empty($this->list)){
			$this->list = SOY2DAO::find("SOYCMS_Page");
		}
	}
	
	/**
	 * @override
	 */
	function parseTree($tree,$depth = 0){
		$list = parent::parseTree($tree,$depth);
		usort($list,array($this,"sortPages"));
		return $list;
	}
	
	/**
	 * ページを並び替える(usort function)
	 */
	function sortPages($a,$b){
		$order_a = $a["object"]["page_order"];
		$order_b = $b["object"]["page_order"];
		return ($order_a > $order_b);
	}
	
	function populateItem($entity,$key,$depth,$isLast){
		
		if(!$entity instanceof SOYCMS_Page){
			$entity = new SOYCMS_Page();
		}
		
		$class = "lv" . $depth;
		if($depth > 1)$class.=" child";
		if(strpos($entity->getType(),".")!==false)$class.=" hidden-page";
		if($entity->isDirectory()){
			$class.=" type-dir";
		}else if(strpos($entity->getUri(),"index.html")!==false){
			$class .= " type-index";
		}else{
			$class.=" type-page";
		}
		
		$this->addModel("page_list_wrap",array(
			"class" => $class,
			"attr:id" => "page-" . $entity->getId()
		));
		
		$this->addLink("detail_link",array("link"=>
			$this->detailLink . "/" .$entity->getId()));
		$this->addLink("config_link",array("link"=>
			$this->detailLink . "/" .$entity->getId(). "#tab1"));
		
		$this->addLink("item_link",array("link"=>
			$this->detailLink . "/" .$entity->getId() . "#tpl_config"));	
		$this->addLink("template_link",array("link"=>
			$this->detailLink . "/" .$entity->getId() . "#tab3"));
		$this->addLink("template_edit_link",array(
			"link" => $this->pageLink . "template/detail?id=" .$entity->getTemplate() . "#tpl_config",
			"attr:class" => $entity->getTemplate()
		));
		
		
			
		$this->addModel("is_directory",array(
			"visible" => $entity->isDirectory()
		));
		
		$this->addModel("is_not_directory",array(
			"visible" => $entity->getType() != "detail"
		));
			
		$this->addLink("list_link",array("link"=>
			$this->entryLink . "/list/" .$entity->getId()));
			
		$this->addLink("copy_link",array("link"=>
			$this->pageLink . "copy/" . $entity->getId()
		));
		$this->addModel("copy_link_wrap",array(
			"visible" => ($entity->getUri() != "_home") 
		));
			
		$this->addLabel("page_name",array(
			"text" => (strpos($entity->getUri(),"index.html") !== false) 
				? "index.html (".mb_strimwidth($entity->getName(),0,20,"...","UTF-8").")"  
				: $entity->getName() 
		));
		$this->addLabel("sitemap_page_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("page_uri",array(
			"text" => $entity->getUri()
		));
		
		$this->addLink("create_page_link",array("link"=>
			$this->pageLink . "create?type=page&parent=" . $entity->getId()
		));
		
		$this->addLink("create_directory_link",array("link"=>
			$this->pageLink . "create?parent=" . $entity->getId()
		));
		
		$this->addLink("remove_link",array(
			"link" => $this->pageLink . "remove/" .$entity->getId(),
		));
			
		$this->addLink("create_entry_link",array(
			"link" => $this->entryLink . "/create/" . $entity->getId()
		));
		
		$this->addLink("dynamic_edit_link",array(
			"link" => soycms_get_page_url($entity->getUri()) . "?dynamic&SOYCMS_SSID=" . session_id()
		));
		
		$this->addLink("public_link",array(
			"link" => soycms_get_page_url($entity->getUri())
		));
		
		$this->addLink("edit_entry_link",array(
			"link" => $this->entryLink . "/page/" . $entity->getId()
		));
		
		$this->addLabel("entry_count",array(
			"text" => ($entity->isDirectory()) ? $this->getEntryCount($entity->getId()) : "-",
			"visible" => ($entity->isDirectory())
		));
		
		$config = $entity->getConfigObject();
		$this->addImage("page_icon",array(
			"src" => (strlen(@$config["icon"])>0) ? soycms_union_uri(SOYCMS_ROOT_URL,"content/" . SOYCMS_LOGIN_SITE_ID . "/" . @$config["icon"]) : "",
			"visible" => (strlen(@$config["icon"])>0)
		));
		
		$id = (is_numeric($entity->getId())) ? $entity->getId() : -1;
		$this->addCheckbox("page_check",array(
			"name" => $this->getCheckboxName(),
			"value" => $entity->getId(),
			"label" => $entity->getName(),
			"selected" => (is_array($this->selected)) ? in_array($id,$this->selected) : ($this->selected == $id)
		));
		
		$this->addInput("page_order",array(
			"name" => "PageOrders[".$entity->getId()."]",
			"value" =>  (!isset($config["order"])) ? 0 : $config["order"]
		));
		
		if($this->getType() && $this->getType() != $entity->getType()){
			return false;
		}
		
		//[.]の含まれる種別は表示しない
		if(strpos($entity->getType(),".") !== false && !isset($_GET["showall"])){
			return false;
		}
			
	}
	
	function getEntryCount($id){
		static $_dao;
		if(!$_dao){
			$_dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		}
		
		if(is_numeric($id)){
			return $_dao->countByDirectory($id);
		}
		
		return 0;
	}
	
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}

	function getCheckboxName() {
		return $this->checkboxName;
	}
	function setCheckboxName($checkboxName) {
		$this->checkboxName = $checkboxName;
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}
?>