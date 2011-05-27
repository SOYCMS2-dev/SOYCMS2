<?php

class HistoryList extends HTMLList{
	
	private $link;
	private $admins = array();
	
	function init(){
		$this->link = soycms_create_link("/page/history/detail/");
		
		$admins = SOY2DAOFactory::create("SOYCMS_UserDAO")->map();
		$this->admins = $admins; 	
	}
	

	function populateItem($entity){
		
		$this->addLabel("history_username",array("text" => @$this->admins[$entity->getAdminId()]));
		
		$this->addLabel("history_type_text",array("text" => $entity->getTypeText()));
		
		$this->addLabel("history_date",array("text" => $entity));
		
		$this->addLink("detail_link",array(
			"link" => $this->link . $entity->getId()
		));
		
	}
}
?>