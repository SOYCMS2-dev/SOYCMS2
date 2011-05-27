<?php
/**
 * @title トラックバック一覧
 */
class page_entry_trackback_index extends SOYCMS_WebPageBase{
	
	private $limit = 10;
	
	function doPost(){
		//状態変更
		//未読
		//公開
		//非公開
		//削除
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		
		if(isset($_POST["trackbackIds"])){
			if(!is_array($_POST["trackbackIds"]))$_POST["trackbackIds"] = array();
			$_POST["trackbackIds"] = array_unique(array_map(create_function('$a','return (int)$a;'),$_POST["trackbackIds"]));
		}
		
		if(isset($_POST["do_unread"])){
			$dao->updateStatus(-1,$_POST["trackbackIds"]);
		}
		
		if(isset($_POST["do_open"])){
			$dao->updateStatus(1,$_POST["trackbackIds"]);
		}
		
		if(isset($_POST["do_close"])){
			$dao->updateStatus(0,$_POST["trackbackIds"]);
		}
		if(isset($_POST["do_remove"])){
			$dao->deleteByIds($_POST["trackbackIds"]);
		}
		
		$this->jump("/entry/trackback?updated");
	}

	function page_entry_trackback_index(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addSelect("status_select",array(
			"name" => "status",
			"options" => array(
				-1 => "未読",
				0 => "非公開",
				1 => "公開"
			),
			"selected" => @$_GET["status"]
		));
		
		$this->createAdd("trackback_list","_class.list.EntryTrackbackList",array(
			"list" => $this->getEntryTrackbacks()
		));
		
		$total = $this->getEntryTrackbackCount();
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/entry/index?page=")
		));
		
	}
	
	function getEntryTrackbackCount(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		return (isset($_GET["status"]) && strlen($_GET["status"])>0) ? $dao->countByStatus($_GET["status"]) : $dao->count();	
	}
	
	function getEntryTrackbacks(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		
		$dao->setLimit($this->limit);
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		$dao->setOffset(($page-1) * $this->limit);
		$list = (isset($_GET["status"]) && strlen($_GET["status"])>0) ? $dao->getByStatus($_GET["status"]) : $dao->get();
		
		return $list;
	}
}