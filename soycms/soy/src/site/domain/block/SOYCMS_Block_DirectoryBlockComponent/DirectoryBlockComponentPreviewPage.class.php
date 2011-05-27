<?php

class DirectoryBlockComponentPreviewPage extends HTMLPage{
	
	private $res = array();
	private $directories = array();
	
	function DirectoryBlockComponentPreviewPage($res){
		$this->res = $res;
		HTMLPage::HTMLPage();
	}
	
	function main(){
		$this->createAdd("entry_list","DirectoryBlockComponentPreviewPage_EntryList",array(
			"list" => $this->res
		));
		
		$directories = array();
		foreach($this->directories as $id){
			try{
				$directories[] = SOY2DAO::find("SOYCMS_Page",($id));
			}catch(Exception $e){
				//
			}
			
		}
		
		$this->createAdd("entry_create_list","HTMLList",array(
			"list" => $directories,
			'populateItem:function($obj)' => 
				'$this->createAdd("create_link","HTMLLink",array("link"=>"'.SOY2PageController::createLink("Entry.Create.").'".$obj->getId()));' .
				'$this->createAdd("page_name","HTMLLabel",array("text"=>$obj->getName()));'
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}

	function getDirectories() {
		return $this->directories;
	}
	function setDirectories($directories) {
		$this->directories = $directories;
	}
}

class DirectoryBlockComponentPreviewPage_EntryList extends HTMLList{
	
	function populateItem($entity){
		if(!is_object($entity))$entity = new SOYCMS_Entry();
		$this->createAdd("detail_link","HTMLLink",array(
			"link" => SOYCMS_SITE_ADMIN_URL . "/Entry/Detail/" . $entity->getId(),
			"text" => $entity->getTitle()
		));
	}
}
?>