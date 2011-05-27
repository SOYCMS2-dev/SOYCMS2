<?php

class page_entry_history_list extends SOYCMS_WebPageBase{

	private $id;
	private $entry;
	private $dir;
	
	private $limit = 7;
	private $page = 1;
	private $total = 0;
	
	function init(){
		$this->id = $_GET["entryId"];
		if(isset($_GET["page"]))$this->page = $_GET["page"];
		
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		
		try{
			$this->dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			$this->dir = new SOYCMS_Page();
		}
	}

	function page_entry_history_list($args) {
		
		WebPage::WebPage();
		
		//記事情報
		$this->addLabel("entry_title",array(
			"text" => $this->entry->getTitle()
		));
		
		//履歴の取得
		$histories = $this->getHistories();
		$this->createAdd("history_list","_class.list.EntryHistoryList",array(
			"list" => $histories,
			"mode" => "ajax"
		));
		
		//build pager
		$this->addPager("pager",array(
			"start" => ($this->page - 1) * $this->limit + 1,
			"page" => $this->page,
			"total" => $this->total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/history/list?entryId=".$this->id."&page=")
		));
		
	}
	
	function getHistories(){
		$res = array();
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryHistoryDAO");
		$this->total = $dao->countHistoryByEntryId($this->entry->getId());
		
		$dao->setLimit($this->limit);
		$dao->setOffset(($this->page - 1)  * $this->limit);
		
		
		$res = $dao->listByEntryId($this->entry->getId());
		
		return $res;
	}
	
	function getLayout(){
		return "blank.php";
	}
}
?>