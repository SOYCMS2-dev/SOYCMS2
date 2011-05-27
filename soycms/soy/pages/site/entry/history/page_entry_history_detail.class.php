<?php

class page_entry_history_detail extends SOYCMS_WebPageBase{

	private $id;
	private $revision;
	private $entry;
	private $history;
	private $dir;
	
	function doPost(){
		
		if(isset($_POST["remove"])){
			$this->history->delete();
			
			$this->jump("/entry/detail/" . $this->id . "?deleted");
		}
		
		if(isset($_POST["do_rollback"])){
			
			//revert前のデータを保存
			SOYCMS_EntryHistory::addHistory($this->entry,"revert");
			
			$this->entry->setTitleSection($this->history->getTitle());
			$this->entry->setSections($this->history->getSections());
			
			//書き出しは行わない
			$this->entry->save();
			
			
			
			
			$this->jump("/entry/detail/" . $this->id . "?updated");
		}
		
		exit;
		
	}
	
	function init(){
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		$this->history = SOY2DAO::find("SOYCMS_EntryHistory",$this->revision);
		
		try{
			$this->dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			$this->dir = new SOYCMS_Page();
		}
		
	}

	function page_entry_history_detail($args) {
		list($this->id,$this->revision) = $args;
		
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildScript();
		
		$this->addForm("form");
		$this->addModel("save_btn",array("name"=>"do_rollback"));
		
	}
	
	function buildPage(){
		$dir = $this->dir;
		$entry_history = $this->history;
		
		
		$editLink = soycms_create_link("/entry/detail/" . $this->id);
		
		$this->addLink("detail_link",array(
			"link" => $editLink
		));
		
		$this->addModel("is_public_entry",array(
			"visible" => $this->entry->getPublish()
		));
		
		$this->addLabel("title_text",array("text" => $this->entry->getTitle()));
		$this->addLabel("history_title_text",array("text" => $this->history->getTitle()));
		$this->addModel("title_changed",array(
			"visible" => $this->entry->getTitle() != $this->history->getTitle()
		));
		$this->addLink("preview_link",array("link" => $editLink . "?preview=" . $this->revision));
		$this->addLabel("entry_url",array(
			"text" => rawurldecode(soycms_union_uri(soycms_get_page_url($dir->getUri()),$this->entry->getUri()))
		));
		
		$this->addLabel("history_username",array(
			"text" => $this->getAdminName($this->history->getAdminId())
		));
		
		$this->addLabel("history_date",array(
			"text" => $this->history
		));
		
		$sections = SOYCMS_EntrySection::unserializeSection($entry_history->getSections());
		
		if(empty($sections)){
			$section = new SOYCMS_EntrySection();
			$section->setType("wysiwyg");
			$section->setValue("");
			$section->setContent("");
			$sections = array(
				$section
			);
		}
		
		foreach($sections as $key => $section){
			$sections[$key]->setType("preview");
		}
		
		$this->createAdd("section_list","entry.page_entry_editor",array(
			"arguments" => array($sections)
		));
		
		
		//最新の履歴を取得する
		$historyDAO = SOY2DAOFactory::create("SOYCMS_EntryHistoryDAO");
		$historyDAO->setLimit(1);
		$histories = $historyDAO->listByEntryId($this->entry->getId());
		
		$this->addModel("not_recent_history",array(
			"visible" => (count($histories) < 1 || $histories[0]->getId() != $this->history->getId())
		));
		
	}
	
	function getAdminName($id){
		try{
			$admin = SOY2DAOFactory::create("SOYCMS_UserDAO")->getById($id);
			return $admin->getName(); 	
		}catch(Exception $e){
			return "User#" . $id;
		}
	}
	
	/**
	 * JavaScript関連を作成
	 */
	function buildScript(){
		
		$scripts = array();
		$scripts[] = "var EDITOR_ACTION_URL = \"".SOY2FancyURIController::createLink("/entry/editor")."?entryId=".$this->id."\";";
		$scripts[] = "var EDITOR_HISTORY_URL = \"".SOY2FancyURIController::createLink("/entry/history/list")."?entryId=".$this->id."\";";
		
		$this->createAdd("ajax_action","HTMLScript",array(
			"script" => implode("\n",$scripts)
		));
	}
	
	function getSubMenu(){
		
		$menu = SOY2HTMLFactory::createInstance("entry.history.page_entry_history_submenu",array(
			"arguments" => array($this->id,$this->entry,$this->revision)
		));
		$menu->display();
	}
}
?>