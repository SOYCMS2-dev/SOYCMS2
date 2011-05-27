<?php


/**
 * @title ラベル一覧
 */
class page_page_label_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["DisplayOrder"])){
			$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
			
			$counter = 0;
			foreach($_POST["DisplayOrder"] as $id => $order){
				$labelDAO->updateDisplayOrder($id,$counter);
				$counter++;
			}
		}
		
		$this->jump("/page/label?updated");
	}

	function page_page_label_index(){
		WebPage::WebPage();
		
		$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
		$labels = $labelDAO->get();
		
		$common = array();
		$dirs = array();
		foreach($labels as $label){
			if($label->isCommon()){
				$common[$label->getId()] = $label;
			}else{
				if(!isset($dirs[$label->getDirectory()]))$dirs[$label->getDirectory()] = array();
				$dirs[$label->getDirectory()][$label->getId()] = $label;
			}
		}
		
		$this->addForm("form");
		
		$this->createAdd("common_label_list","_class.list.LabelList",array(
			"list" => $common
		));
		
		$this->createAdd("tag_list","_class.list.EntryTagList");
		
		$this->createAdd("label_tree","_class.list.LabelTreeComponent",array(
			"labels" => $dirs
		));
	}
	
}