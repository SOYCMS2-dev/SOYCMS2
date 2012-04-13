<?php
/**
 * @title 記事の検索
 */
class page_entry_search extends SOYCMS_WebPageBase{
	
	private $total = 0;
	private $limit = 10;
	private $page = 1;
	private $sort = "update";

	function page_entry_search(){
		WebPage::WebPage();
		
		$this->page = (@$_GET["page"]) ? $_GET["page"] : 1;
		DisplayPlugin::hide("sort_type_order");
		
		//検索フォームの作成
		$this->buildForm();
		
		//記事を取得
		$entries = $this->getEntries();
		$this->createAdd("entry_list","_class.list.EntryList",array(
			"list" => $entries,
		));
		
		
		//ページャの作成
		$total = $this->getEntryCount();
		$page = $this->page;
		
		unset($_GET["page"]);
		$query = http_build_query($_GET);
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/search?".$query."&page=")
		));
	}
	
	function buildForm(){
		$this->createAdd("searchform","_class.form.EntrySearchForm");
		$this->addForm("form");
	}
	
	/**
	 * 検索実行
	 */
	function getEntries(){
		$res = array();
		
		$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntrySearchLogic");
		list($query,$binds) = $logic->buildSearchQuery(
			array(),
			@$_GET["labels"],
			true,
			((isset($_GET["tags"])) ? $_GET["tags"] : array()),
			@$_GET["tagOption"]
		);
		
		$wheres  = array();
		
		if(isset($_GET["status"]) && strlen($_GET["status"]) > 0){
			$wheres[] = "entry_status = :status";
			$binds[":status"] = $_GET["status"];
		}
		
		if(isset($_GET["word"]) && strlen($_GET["word"]) > 0){
			$wheres[] = "(title LIKE :text OR content LIKE :text)";
			$binds[":text"] = "%" . $_GET["word"] . "%";
		}
		
		if(strlen($query->where) > 0 && count($wheres) > 0)$query->where .= " AND ";
		$query->where .= implode(" AND ", $wheres);
		$dao = $logic->getEntryDAO();
		$total = clone($query);
		$total->sql = "count(id) as entry_count";
		$total->group = null;
		$total->having = null;
		
		$result = $dao->executeQuery($total,$binds);
		$this->total = $result[0]["entry_count"];
		
		$dao->setLimit($this->limit);
		$dao->setOffset(($this->page-1) * $this->limit);
		$result = $dao->executeQuery($query,$binds);
		
		foreach($result as $row){
			$res[] = $dao->getById($row["id"]);
		}
		
		
		
		return $res;
	}
	
	function getEntryCount(){
		return $this->total;
	}
}