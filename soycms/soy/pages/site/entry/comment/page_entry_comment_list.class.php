<?php
/**
 * @title コメント、記事毎に表示
 */
class page_entry_comment_list extends SOYCMS_WebPageBase{
	
	private $limit = 10;
	private $entry;
	private $comments;
	
	function init(){
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		$this->comments = SOY2DAO::find("SOYCMS_EntryComment",array("entryId"=>$this->id));
	}
	
	function doPost(){
		
		//コメントの投稿
		if(isset($_POST["EntryComment"]) && isset($_POST["do_reply"])){
			$comment = SOY2::cast("SOYCMS_EntryComment",$_POST["EntryComment"]);
			$comment->setEntryId($this->id);
			$comment->save();
		}
		
		//状態変更
		//未読
		//公開
		//非公開
		//削除
		$dao = SOY2DAOFactory::create("SOYCMS_EntryCommentDAO");
		
		if(isset($_POST["commentIds"])){
			if(!is_array($_POST["commentIds"]))$_POST["commentIds"] = array();
			$_POST["commentIds"] = array_unique(array_map(create_function('$a','return (int)$a;'),$_POST["commentIds"]));
		}
		
		if(isset($_POST["do_unread"])){
			$dao->updateStatus(-1,$_POST["commentIds"]);
		}
		
		if(isset($_POST["do_open"])){
			$dao->updateStatus(1,$_POST["commentIds"]);
		}
		
		if(isset($_POST["do_close"])){
			$dao->updateStatus(0,$_POST["commentIds"]);
		}
		if(isset($_POST["do_remove"])){
			$dao->deleteByIds($_POST["commentIds"]);
		}
		
		$this->jump("/entry/comment/list/" . $this->id . "?updated");
		
	}

	function page_entry_comment_list($args){
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildScript();
	}
	
	function buildPage(){
		$this->addLabel("entry_title",array("text"=>$this->entry->getTitle()));
		$this->addLabel("entry_content",array("text"=>mb_strimwidth(strip_tags($this->entry->getContent()),0,300,"...")));
		
		$this->createAdd("comment_list","_class.list.EntryCommentList",array(
			"list" => $this->comments,
		));
		
		$this->addForm("form");
		$this->createAdd("comment_form","_class.form.EntryCommentForm");
	}
	
	/**
	 * JavaScript関連を作成
	 */
	function buildScript(){
		
		$scripts = array();
		$scripts[] = "var EDITOR_ACTION_URL = \"".SOY2FancyURIController::createLink("/entry/editor")."?entryId=".$this->id."\";";
		
		$this->createAdd("ajax_action","HTMLScript",array(
			"script" => implode("\n",$scripts)
		));
	}
	
	function getSubMenu(){
		
		$menu = SOY2HTMLFactory::createInstance("entry.page_entry_detail_submenu",array(
			"arguments" => array($this->id,$this->entry,"comment"),
		));
		$menu->display();
	}
	
}