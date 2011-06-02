<?php
/**
 * @title 記事の複製
 */
class page_entry_copy extends SOYCMS_WebPageBase{
	
	function doPost(){
		$entry = $this->entry;
		
		try{
			if(empty($_POST["directory"]))throw new Exception("");
			
			$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntryLogic");
			$id = $logic->copy($entry,$_POST["directory"]);
			
			$this->jump("/entry/detail/" . $id . "?created");
		}catch(Exception $e){
			$this->jump("/entry/copy/" . $entry->getId() . "?failed");
		}
	}
	
	private $entry;
	
	function init(){
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
	}

	function page_entry_copy($args){
		$this->id = $args[0];
		
		WebPage::WebPage();
		
		$this->addForm("form");
		
		//ツリーを表示
		$this->createAdd("directory_tree","_class.list.EntryTreeComponent",array(
		));
	}
}