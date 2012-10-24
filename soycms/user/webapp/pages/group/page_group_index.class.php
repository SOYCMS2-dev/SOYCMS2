<?php

class page_group_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
	}
	
	private $page = 1;
	private $limit = 20;

	function page_group_index() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
	}
	
	function buildForm(){
		
	}
	
	function buildPage(){
		//検索
		list($result,$total) = $this->doSearch();
		$this->createAdd("group_list","_class.list.GroupList",array(
			"list" => $result
		));
		
		
		//ページャ
		$this->addPager("pager",array(
			"start" => ($this->page - 1) * $this->limit + 1,
			"page" => $this->page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/list/?page=")
		));
	}
	
	function doSearch(){
		$dao = SOY2DAOFactory::create("Plus_GroupDAO");
		
		$total = $dao->count();
		
		$dao->setLimit($this->limit);
		$dao->setOffset(($this->page - 1) * $this->limit);
		$result = $dao->get();
		
		return array($result,$total);
	}

}
?>