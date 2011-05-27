<?php

class EntryTagList extends HTMLList{
	
	function init(){
		if(!$this->list){
			$dao = SOY2DAOFactory::create("SOYCMS_Tag");
			$this->list = $dao->getTagList();	
		}
	}
	
	function populateItem($entity){
		
		$this->addLabel("tag_text",array("text" => $entity));
		
	}
	
}


?>