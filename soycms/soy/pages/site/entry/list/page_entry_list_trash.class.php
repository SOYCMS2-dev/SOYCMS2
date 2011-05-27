<?php

/**
 * @title ゴミ箱に入っている記事（完全削除）
 */
class page_entry_list_trash extends SOYCMS_WebPageBase{
	
	private $limit = 30;
	private $sort = "update";
	
	function doPost(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		if(isset($_POST["entryIds"])){
			$entryIds = (is_array($_POST["entryIds"])) ? $_POST["entryIds"] : explode(",",$_POST["entryIds"]);
			
			
			if(isset($_POST["recover"])){
				$dao->begin();
				foreach($entryIds as $entryId){
					$dao->updatePublishById($entryId,0);
				}
				$dao->commit();
			}
			
			if(isset($_POST["remove"])){
				$dao->begin();
				foreach($entryIds as $entryId){
					$dao->delete($entryId);
				}
				$dao->commit();
			}
		}
		
		$this->jump("/entry/list/trash?updated");
		
		
	}
	
	function page_entry_list_trash(){
		WebPage::WebPage();
		
		$this->buildPage();
		
		$this->addForm("form");
		
		$total = $this->getEntryCount();
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/list/trash?page=")
		));
		
	}
	
	function buildPage(){
		
		//記事を取得
		$entries = $this->getEntries();
		$this->createAdd("entry_list","_class.list.EntryList",array(
			"list" => $entries,
		));
		
		DisplayPlugin::hide("sort_type_order");
		
	}
	
	function getEntryCount(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		return $dao->countByPublishStatus(-1);	
	}
	
	function getEntries(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setLimit($this->limit);
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		$dao->setOffset(($page-1) * $this->limit);
		
		if($this->sort == "order"){
			$dao->setOrder("display_order");
		}
		
		$list = $dao->getTrashEntry();
		
		return $list;
	}
}