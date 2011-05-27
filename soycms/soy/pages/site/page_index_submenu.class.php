<?php

class page_index_submenu extends SOYCMS_WebPageBase{

	function page_index_submenu() {
		WebPage::WebPage();
		
		$this->buildPage();
		
	}
	
	function buildPage(){
		
		/* サイトの状態 */
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		$this->addLabel("entry_count",array(
			"text" => $dao->count()
		));
		
		$this->addLabel("close_entry_count",array(
			"text" => $dao->countByStatus('close')
		));
		
		$count = $dao->countByStatus('review');
		$this->addLabel("review_entry_count",array(
			"text" => $count
		));
		$this->addModel("review_entry",array(
			"visible" => ($count>0)
		));
		
		/* コメント／トラックバック */
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryCommentDAO");
		$commentCount = $dao->countByStatus(-1);
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		$tbCount = $dao->countByStatus(-1);
		
		$this->addModel("newitem_exists",array(
			"visible" =>  ($commentCount + $tbCount) > 0   	
		));
		
		$this->addModel("no_newitem",array(
			"visible" => ($commentCount + $tbCount) == 0
		));
		
		$this->addLabel("comment_count",array(
			"text" => $commentCount
		));
		
		$this->addLabel("trackback_count",array(
			"text" => $tbCount
		));
		
		/* 最近の活動 */
		$activity = array();
		
		//最近更新した記事を10件
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setLimit(10);
		$entries = $dao->getRecentEntries();
		$link = soycms_create_link("/entry/detail/");
		foreach($entries as $entry){
			$activity[] = array(
				"label" => $entry->getTitle(),
				"time" => $entry->getUpdateDate(),
				"link" => $link . $entry->getId()
			);
		}
		
		//最近更新したページを10件取得
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$dao->setOrder("update_date desc");
		$dao->setLimit(10);
		$pages = $dao->get();
		$link = soycms_create_link("/page/detail/");
		foreach($pages as $page){
			$activity[] = array(
				"label" => $page->getName(),
				"time" => $page->getUpdateDate(),
				"link" => $link . $page->getId()
			);
		}
		
		//テンプレート、スニペット、モジュールの最終更新時刻
		$templates = SOYCMS_Template::getList();
		$link = soycms_create_link("/page/template/detail?id=");
		foreach($templates as $page){
			$activity[] = array(
				"label" => $page->getName(),
				"time" => $page->getUpdateDate(),
				"link" => $link . $page->getId()
			);
		}
		
		$snippet = SOYCMS_Snippet::getList();
		$link = soycms_create_link("/page/snippet/detail?id=");
		foreach($snippet as $page){
			$activity[] = array(
				"label" => $page->getName(),
				"time" => $page->getUpdateDate(),
				"link" => $link . $page->getId()
			);
		}
		
		$library = SOYCMS_Library::getList();
		$link = soycms_create_link("/page/library/detail?id=");
		foreach($library as $page){
			$activity[] = array(
				"label" => $page->getName(),
				"time" => $page->getUpdateDate(),
				"link" => $link . $page->getId()
			);
		}
		
		$navigation = SOYCMS_Navigation::getList();
		$link = soycms_create_link("/page/navigation/detail?id=");
		foreach($navigation as $page){
			$activity[] = array(
				"label" => $page->getName(),
				"time" => $page->getUpdateDate(),
				"link" => $link . $page->getId()
			);
		}
		
		usort($activity,create_function('$a,$b','return $a["time"] <=  $b["time"];'));
		$activity = array_values($activity);
		
		$html = array();
		
		
		/* ＿人人人人人人人人人人人人人人人人人人人＿　*/
		/* ＞　　　インクリしていってね！！！　　　＜*/
		/* ￣^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ^Ｙ￣ */
		
		for($i=0;$i<10;$i++){
			
			if(strlen($activity[$i]["label"])<1){
				continue;
			}
			
			$html[] = "<li><a href=\"".$activity[$i]["link"]."\">" . $activity[$i]["label"] . "</a>" . 
				"<span class='s'>(".date("Y-m-d",$activity[$i]["time"]).")</span>"; 
		}
		$this->addLabel("activities",array(
			"html" => implode("",$html)
		));
		
	}
	
	function getLayout(){
		return "blank.php";
	}
}

class WidgetList extends HTMLList{
	
	function populateItem($entity){
		$this->addLabel("widget_title",array(
			"title" => $entity["title"]
		));	
		
		$this->addLink("config_link",array(
			"link" => $entity["config_link"],
			"visible" => (strlen($entity["config_link"]))
		));	
		
		$this->addLabel("widget_html",array(
			"title" => $entity["html"]
		));	
	}
	
}
?>