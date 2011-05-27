<?php

class SOYCMS_TemplatePreviewPage extends SOYCMS_SitePageBase{
	
	function SOYCMS_TemplatePreviewPage($args){
		$this->setPageObject($args["page"]);
		
		WebPage::WebPage();
		
	}
	
	function isModified(){
		return true;
	}
	
	function build($args){
		
		$entry = new SOYCMS_Entry();
		$entry->setTitle("プレビュータイトル");
		$entry->setContent("<p>".str_repeat("テキスト<br />",10)."</p>");
		
		$this->setEntryTitle($entry->getTitle());
		
		//block:entry
		$this->createAdd("entry","SOYCMS_EntryListComponent",array(
			"list" => array($entry),
			"soy2prefix" => "block",
			"mode" => "detail",
			"link" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/entry/detail/" . $entry->getId() : ""
		));
		
		$this->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => array($entry,$entry,$entry),
			"soy2prefix" => "block",
			"mode" => "list",
			"link" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/entry/detail/" . $entry->getId() : ""
		));
		
		//ディレクトリの情報
		$this->addLabel("directory_name",array(
			"soy2prefix" => "cms",
			"text" => "ダミーのディレクトリ"
		));
		$this->addLink("directory_link",array(
			"soy2prefix" => "cms",
			"link" => "javascript:alert(0);"
		));
		
		//記事のタイトル
		$this->addLabel("entry_title",array(
			"text" => $entry->getTitle(),
			"soy2prefix" => "cms",
		));
		
		//次の記事 前の記事
		$this->buildNavigationBlock($entry);
		
		//次のページ
		//前のページ
		$pages = $this->getPages($entry);
		$this->createAdd("pager","SOYCMS_DetailPageBase_HTMLPager",array(
			"pages" => $pages,
			"current" => $entry->getId(),
			"soy2prefix" => "block",
			"childPrefix" => "cms"
		));
		
	
	}
	
	/**
	 * ページナビゲーションの構築
	 */
	function buildPageNavigation(){
		
	}
	
	/**
	 * 次の記事、前の記事の構築
	 */
	function buildNavigationBlock($entry){

		
		try{
			$prev = new SOYCMS_Entry();
			$prev->setTitle("前の記事サンプル");
		}catch(Exception $e){
			$prev = null;
		}
		
		try{
			$next = new SOYCMS_Entry();
			$next->setTitle("前の記事サンプル");
		}catch(Exception $e){
			$next = null;
		}
		
		$pageUri = "javascript:void(0);";
		
		$this->addModel("prev_link_wrap",array("visible" => (!is_null($prev)), "soy2prefix" => "cms"));
		$this->addModel("next_link_wrap",array("visible" => (!is_null($next)), "soy2prefix" => "cms"));
		
		$this->addLink("prev_link",array("link" => "javascript:void(0);", "soy2prefix" => "cms"));
		$this->addLink("next_link",array("link" => "javascript:void(0);", "soy2prefix" => "cms"));
		
		$this->addLabel("prev_entry_title",array("text"=>(($prev)?$prev->getTitle():""),"soy2prefix"=>"cms"));
		$this->addLabel("next_entry_title",array("text"=>(($next)?$next->getTitle():""),"soy2prefix"=>"cms"));
		
	}
	
	function getPages($entry){
		return array();	
	}

}
