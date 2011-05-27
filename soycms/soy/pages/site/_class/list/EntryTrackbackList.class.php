<?php

class EntryTrackbackList extends HTMLList{
	
	private $listLink;
	private $mapping = array();
	
	function init(){
		
		$this->listLink = soycms_create_link("/entry/trackback/list");
		
		$this->_soy2_parent->addModel("trackback_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("trackback_not_exists",array("visible"=>count($this->list)<1));
		
	}

	function populateItem($entity){
 		
 		$this->addCheckbox("trackback_checkbox",array(
 			"name" => "trackbackIds[]",
 			"value" => $entity->getId()
 		));
 		
 		$this->addLink("blog_link",array(
 			"link" => $entity->getUrl()
 		));
 		
 		$url = $entity->getUrl();
 		if(mb_strwidth($url) > 50){
 			$url = mb_strimwidth($url,0,36,"...") . mb_strimwidth($url,mb_strwidth($url) - 14,99999);
 		}
 		
 		$this->addLabel("blog_url",array(
 			"text" => $url
 		));
 		
 		$this->addLabel("blog_name",array(
 			"text" => $entity->getBlogName()
 		));
 		
 		$this->addLabel("trackback_title",array(
 			"text" => $entity->getTitle()
 		));
 		
 		$this->addLabel("entry_title",array(
 			"text" => $entity->getEntryTitle()
 		));
 		
 		$this->addLabel("trackback_status",array(
 			"text" => $entity->getStatusText()
 		));
 		
 		$this->addLink("entry_link",array(
 			"link" => $this->listLink . "/" . $entity->getEntryId()
 		));
 		
 		$this->addLabel("excerpt",array(
 			"text" => mb_strimwidth($entity->getExcerpt(),0,100,"...")
 		));
 		
 		$this->createAdd("trackback_submit_date","_class.component.SimpleDateLabel",array(
 			"date" => $entity->getSubmitDate()
 		));
 		
	}
}
?>