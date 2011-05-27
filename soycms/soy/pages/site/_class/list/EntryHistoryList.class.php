<?php

class EntryHistoryList extends HTMLList{
	
	private $link;
	private $workflow;
	private $admins = array();
	
	function init(){
		$this->link = soycms_create_link("/entry/");
		
		$this->workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		$this->workflow->load();
		
		$admins = SOY2DAOFactory::create("SOYCMS_UserDAO")->map();
		$this->admins = $admins; 	
	}
	

	function populateItem($entity){
		
		$this->addLabel("history_username",array("text" => @$this->admins[$entity->getAdminId()]));
		$this->addLabel("history_status_text",array("text" => $this->workflow->getStatusText($entity->getStatus())));
		
		$this->addLabel("history_type_text",array("text" => $entity->getTypeText()));
		
		$this->addLabel("history_date",array("text" => $entity));
		$this->addLabel("history_comment",array("text" => $entity->getComment()));
		
		$this->addLink("preview_link",array(
			"link" => $this->link . "detail/" . $entity->getEntryId() . "?preview=" . $entity->getId()
		));
		$this->addLink("detail_link",array(
			"link" => $this->link . "history/detail/" . $entity->getEntryId() . "/" . $entity->getId()
		));
		
	}
}
?>