<?php
/**
 * @title 記事一覧
 */
class page_entry_index extends SOYCMS_WebPageBase{
	
	private $limit = 10;

	function page_entry_index(){
		WebPage::WebPage();
		
		//ツリーの表示
		$this->createAdd("page_entry_tree","entry.page_entry_tree");
		
		//新着を取得
		$entries = $this->getEntries();
		$this->createAdd("entry_list","_class.list.EntryList",array(
			"list" => $entries
		));
		
		$total = $this->getEntryCount();
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/index?page=")
		));
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$count_trash = $dao->countByPublishStatus(-1);
		$count_draft = $dao->countByPublishStatus(0);
		$count_review = $dao->countByStatus("review");
		
		$this->addLabel("count_trash",array(
			"text" => $count_trash
		));
		
		$this->addLabel("count_draft",array(
			"text" => $count_draft
		));
		
		$this->addLabel("count_review",array(
			"text" => $count_review
		));
	}
	
	function getEntryCount(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		return $dao->count();	
	}
	
	function getEntries(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setLimit($this->limit);
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		$dao->setOffset(($page-1) * $this->limit);
		$list = $dao->getRecentEntries();
		return $list;
	}
}