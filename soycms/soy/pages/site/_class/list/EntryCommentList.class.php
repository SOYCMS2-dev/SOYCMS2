<?php

class EntryCommentList extends HTMLList{
	
	private $listLink;
	private $mapping = array();
	
	function init(){
		
		$this->listLink = soycms_create_link("/entry/comment/list");
		
		$this->_soy2_parent->addModel("comment_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("comment_not_exists",array("visible"=>count($this->list)<1));
		
	}

	function populateItem($entity){
 		
 		$this->addCheckbox("comment_checkbox",array(
 			"name" => "commentIds[]",
 			"value" => $entity->getId()
 		));
 		
 		$this->addLabel("entry_title",array(
 			"text" => $entity->getEntryTitle()
 		));
 		
 		
 		$this->addLink("list_link",array(
 			"link" => $this->listLink . "/" . $entity->getEntryId() . "#comment" . $entity->getId(),
 		));
 		
 		$this->addLabel("comment_title",array(
 			"text" => $entity->getTitle()
 		));
 		
 		$this->addLabel("comment_status",array(
 			"text" => $entity->getStatusText()
 		));
 		
 		$this->addLabel("comment_content_short",array(
 			"text" => mb_strimwidth($entity->getContent(),0,120,"...")
 		));
 		$this->addLabel("comment_content",array(
 			"text" => nl2br($entity->getContent())
 		));
 		
 		$this->addLabel("comment_author",array(
 			"text" => $entity->getAuthor()
 		));
 		
 		$this->createAdd("comment_submit_date","_class.component.SimpleDateLabel",array(
 			"date" => $entity->getSubmitDate()
 		));
 		
	}
}
?>