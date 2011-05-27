<?php
/**
 * 
 */
class EntryTreeComponent extends HTMLTree{
	
	private $mode = "template";
	private $entryLink;
	private $templates = array();
	private $labels = array();
	
	function init(){
		$this->entryLink = soycms_create_link("/entry");
		$this->tree = SOYCMS_DataSets::get("site.page_tree",array());
		if(empty($this->list)){
			$this->list = SOY2DAO::find("SOYCMS_Page",array("type" => "detail"));
		}
		
		if($this->mode == "template"){
			$dao = SOY2DAOFactory::create("SOYCMS_EntryTemplateDAO");
			$templates = $dao->get();
			
			foreach($templates as $template){
				$dir = $template->getDirectory();
				if(!isset($this->templates[$dir]))$this->templates[$dir] = array();
				$this->templates[$dir][$template->getId()] = $template;
			}
			
			$labels = SOY2DAO::find("SOYCMS_Label");
			$dir_label = array();
			foreach($labels as $label){
				if(!isset($dir_label[$label->getDirectory()]))$dir_label[$label->getDirectory()] = array();
				$dir_label[$label->getDirectory()][$label->getId()] = $label;
			}
			$this->labels = $dir_label;
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
	
	/**
	 * 雛形を作る
	 */
	function getTemplates($id){
		return (isset($this->templates[$id])) ? $this->templates[$id] : array();
	}
	
	/**
	 * @return array
	 */
	function getLabels($dirId){
		return (isset($this->labels[$dirId])) ? $this->labels[$dirId] : array();
	}

	function populateItem($entity,$key,$depth,$isLast){
		
		if(!$entity instanceof SOYCMS_Page){
			$entity = new SOYCMS_Page();
		}
		
		/* common */
		$class = "dir-tree lv" . $depth;
		if($depth > 1)$class.=" child";
		
		$this->addModel("page_list_wrap",array(
			"class" => $class,
			"attr:id" => "page-" . $entity->getId()
		));
		
		/* templates */
		if($this->mode == "template"){
			$templates = $this->getTemplates($entity->getId());
			$this->createAdd("template_list","EntryTemplateTree_TemplateList",array(
				"list" => $templates,
				"entryLink" => $this->entryLink
			));
			$this->addModel("no_template",array(
				"visible" => count($templates) < 1
			));
			$this->addModel("template_exists",array(
				"visible" => count($templates) > 0
			));
			$this->addModel("template_list_more",array(
				"visible" => count($templates) > 3
			));
			$this->addLabel("template_more_count",array(
				"text" => count($templates) - 3
			));
			
			/* label */
			$labels = $this->getLabels($entity->getId());
			$this->createAdd("directory_label_list","EntryTemplateTree_LabelList",array(
				"list" => $labels,
				"entryLink" => $this->entryLink
			));
			$this->addModel("directory_label_exists",array(
				"visible" => count($labels) > 0
			));
		}
		
		/* page */
		
		$this->addLabel("page_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("page_id",array(
			"text" => $entity->getId()
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
		
		/* entry */
		$this->addLink("list_link",array("link"=>
			$this->entryLink . "/list/" .$entity->getId()));
		
		//新規作成  - 白紙
		$this->addLink("create_entry_link",array(
			"link" => $this->entryLink . "/create/" . $entity->getId() . "?blank",
		));
		
		//この項目は並び替えに必須
		//記事のサイトマップがサイトマップの並び替えと一致していなかったバグ
		$this->addInput("page_order",array(
			"value" =>  (!isset($config["order"])) ? 0 : $config["order"]
		));
		
	
			
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

	function getEntryLink() {
		return $this->entryLink;
	}
	function setEntryLink($entryLink) {
		$this->entryLink = $entryLink;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}

class EntryTemplateTree_TemplateList extends HTMLList{
	
	private $entryLink;
	
	function populateItem($entity,$index){
		$this->addLabel("template_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("template_description",array(
			"text" => $entity->getDescription()
		));
		
		$this->addLink("template_detail_link",array(
			"link" => $this->entryLink . "/template/detail/" . $entity->getId()
		));
		
		$this->addLink("create_entry_link",array(
			"link" => $this->entryLink . "/create/" . $entity->getDirectory() . "/" . $entity->getId()
		));
		
		$class = "template_list child";
		
		if($index > 3){
			$class .= " template_list_more";
		}
		
		$this->addModel("template_wrap",array(
			"class" => $class
		));
	}
	
	function getEntryLink() {
		return $this->entryLink;
	}
	function setEntryLink($entryLink) {
		$this->entryLink = $entryLink;
	}
	
}

class EntryTemplateTree_LabelList extends HTMLList{
	
	function populateItem($entity){
		$this->addCheckbox("label_select",array(
			"name" => "label",
			"value" => $entity->getId(),
			"label" => $entity->getName()
		));
	}
	
}