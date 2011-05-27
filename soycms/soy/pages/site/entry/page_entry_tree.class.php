<?php

class page_entry_tree extends HTMLPage{

	function page_entry_tree() {
		HTMLPage::HTMLPage();
		
		//ツリーを表示
		$this->createAdd("directory_tree","_class.list.EntryTreeComponent",array(
			"mode" => "tree"
		));
		
		$this->createAdd("label_list","_class.list.LabelList",array(
			"list" => SOY2DAO::find("SOYCMS_Label",array("type" => 0))
		));
		
		$this->buildPage();
		
	}
	
	function buildPage(){
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
}
?>