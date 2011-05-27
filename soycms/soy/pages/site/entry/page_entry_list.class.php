<?php
SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");

/**
 * @title ディレクトリ単位で記事一覧
 */
class page_entry_list extends SOYCMS_WebPageBase{
	
	protected $id;
	protected $labelId;
	protected $label;
	protected $page;
	protected $limit = 20;
	protected $sort = "update";
	
	function prepare(){
		$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
		
		if($this->page->getType() != "detail"){
			$treePath = SOYCMS_DataSets::get("site.page_tree_path",array());
			if(isset($treePath[$this->page->getId()]) && count($treePath[$this->page->getId()])>1){
				$treePath = $treePath[$this->page->getId()];
				$id = array_pop(array_slice($treePath,count($treePath)-2,1));
				$this->jump("/entry/list/" . $id . "#page_entry");
				
			}else{
				$this->jump("/entry");
			}
		}
		
		parent::prepare();
	}
	
	function init(){
		DisplayPlugin::visible("directory");
		
		try{
			if($this->labelId){
				$this->label = SOY2DAO::find("SOYCMS_Label",$this->labelId);
				DisplayPlugin::hide("directory");
			}
		}catch(Exception $e){
			$this->jump("/entry/list/" . $this->id);
		}	
	}

	function page_entry_list($args){
		$this->id = @$args[0];
		$this->labelId = @$args[1];
		if(isset($_GET["sort"]))$this->sort = $_GET["sort"];
		
		WebPage::WebPage();
		
		//ツリーの表示
		$this->createAdd("page_entry_tree","entry.page_entry_tree");
		
		$this->buildPage();
		$this->buildForm();
		
		$total = $this->getEntryCount();
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/list/".$this->id."/?sort=".$this->sort."&page=")
		));
		
		$this->buildPageEntry();
	}
	
	function buildForm(){
		$this->addInput("directory_id",array(
			"name" => "directory_id",
			"value" => $this->page->getId()
		));	
		
		$this->addLink("view_update",array(
			"link" => ($this->sort == "update") ? "" : "?sort=update",
			"html" => ($this->sort == "update") ? "<strong>更新順</strong>" : "更新順",
		));
		$this->addLink("view_order",array(
			"link" => ($this->sort == "order") ? "" : "?sort=order",
			"html" => ($this->sort == "order") ? "<strong>表示順</strong>" : "表示順",
		));
		
	}
	
	function buildPage(){
		
		$pages = $this->getPages();
		
		$mapping = SOYCMS_DataSets::get("site.page_tree_path",array());
		$tree = $mapping[$this->page->getId()];
		
		if($this->label){
			$pages[":label"] = $this->label;
			$tree[] = ":label"; 
		}
		
		$this->createAdd("page_topic_path","SOYCMS_EntryTopicPath",array(
			"list" => $pages,
			"type" => "detail",
			"treeIds" => $tree
		));
		
		$this->addLabel("page_name",array("text" => $this->page->getName()));
		$this->addLink("page_url",array(
				"text"=>$this->page->getUri(),
				"link" => soycms_get_page_url($this->page->getUri())
		));
		
		$this->addLink("page_config_link",array("link" => soycms_create_link("/page/detail/" . $this->id ."#tab1")));
		$this->addLink("index_config_link",array("link" => soycms_create_link("/page/detail/" . $this->id ."?index")));
		$this->addLink("index_config_link",array("link" => soycms_create_link("/page/detail/" . $this->id ."?index")));
		$this->addLink("entry_create_link",array("link" => soycms_create_link("/entry/create/" . $this->id)));
		$this->addLink("entry_export_link",array("link" => soycms_create_link("/entry/export/" . $this->id)));
		$this->addLink("entry_import_link",array("link" => soycms_create_link("/entry/import/" . $this->id)));	
		
		//記事を取得
		$entries = $this->getEntries();
		$this->createAdd("entry_list","_class.list.EntryList",array(
			"list" => $entries,
		));
		
		if($this->sort == "order"){
			DisplayPlugin::visible("sort_type_order");
		}else{
			DisplayPlugin::hide("sort_type_order");
		}
		
		$this->addLabel("current_directory_id",array(
			"text" => $this->page->getId() 
		));
		
	}
	
	/**
	 * ページと対応する記事を表示
	 */
	function buildPageEntry(){
		$pageId = $this->page->getId();
		$mapping = SOYCMS_DataSets::get("site.page_relation",array());
		$pages = SOYCMS_DataSets::get("site.page_mapping",array());
		$ids = (isset($mapping[$pageId])) ? $mapping[$pageId] : array();
		
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$entries = array();
		
		if(!is_array($ids)){
			$ids = array();
		}
		
		foreach($ids as $id){
			$page = $pages[$id];
			if($page["type"] == "detail" || $page["type"][0] == ".")continue;
			
			//publicとか
			$entry = $entryDAO->getAllByDirectory($id);
			if(count($entry) != 1){
				foreach($entry as $obj){
					$obj->delete();
				}
				$entry = new SOYCMS_Entry();
				$entry->setDirectory($id);
				$entry->setTitle($page["name"]);
				$entry->setUri("");
				$entry->save();
				$entries[] = $entry;
			}else{
				$entries[] = array_shift($entry);
			}
		}
		
		$this->createAdd("page_entry_list","_class.list.EntryList",array(
			"list" =>$entries,
		));
		
	}
	
	/**
	 * ページを取得
	 */
	function getPages(){
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		return $pages;
	}
	
	function getEntryCount(){
		if($this->label){
			$dao = SOY2DAOFactory::create("SOYCMS_EntryLabelDAO");
			return $dao->countByLabelId($this->labelId);
		}else{
			$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
			return $dao->countByDirectory($this->id);
		}
	}
	
	function getEntries(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setLimit($this->limit);
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		$dao->setOffset(($page-1) * $this->limit);
		
		//ラベル指定
		if($this->label){
			$dirs = ($this->label->getDirectory()) ? array($this->label->getDirectory()) : array();
			$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntrySearchLogic");
			list($query,$binds) = $logic->buildSearchQuery($dirs,array($this->labelId));
			
			$array = $dao->executeQuery($query,$binds);
			$list = array();
			foreach($array as $row){
				$list[$row["id"]] = $dao->getById($row["id"]);
			}
			
		//通常
		}else{
			
			
			if($this->sort == "order"){
				$dao->setOrder("display_order");
			}
			$list = $dao->getByDirectory($this->id);
		}
		return $list;
	}
}

class SOYCMS_EntryTopicPath extends HTMLTree{
	private $listLink;
	
	function init(){
		$this->listLink = soycms_create_link("/entry/list");
	}
	function populateItem($entity,$key,$depth,$isLast){
		
		$this->addLink("list_link",array("link"=>
			(!$isLast) ? $this->listLink . "/" .$entity->getId() : ""
		));
		
		$this->addLabel("page_name",array(
			"html" => ($isLast) ? "<strong>" . $entity->getName() . "</strong>" : $entity->getName()
		));
	}
}