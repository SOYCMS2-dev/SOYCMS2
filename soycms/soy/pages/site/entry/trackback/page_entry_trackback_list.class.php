<?php
/**
 * @title 記事毎トラックバック一覧
 */
class page_entry_trackback_list extends SOYCMS_WebPageBase{
	
	private $id;
	private $limit = 10;
	
	function init(){
		try{
			$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		}catch(Exception $e){
			$this->jump("/entry/trackback");
		}
	}
	
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
		
		$this->jump("/entry/trackback/list/" . $this->id . "?updated");
		
	}

	function page_entry_trackback_list($args){
		$this->id = $args[0];
		WebPage::WebPage();
		
		//記事情報
		$this->addLabel("entry_title",array(
			"text" => $this->entry->getTitle()
		));
		
		$this->addForm("form");
		
		$this->createAdd("trackback_list","_class.list.EntryTrackbackList",array(
			"list" => $this->getEntryTrackbacks()
		));
		
		
	}
	
	function getEntryTrackbacks(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		return $dao->getByEntryId($this->id);
	}
	
	
	function getSubMenu(){
		
		$menu = SOY2HTMLFactory::createInstance("entry.page_entry_detail_submenu",array(
			"arguments" => array($this->id,$this->entry,"comment"),
		));
		$menu->display();
	}
}