<?php

class UserList extends HTMLList{
	
	private $detailLink;
	private $removeLink;
	private $session;
	
	function init(){
		$this->detailLink = SOY2FancyURIController::createLink("/user/detail");
		$this->removeLink = SOY2FancyURIController::createLink("/user/remove");
		$this->session = $session = SOY2Session::get("base.session.UserLoginSession");;
	}
	
	function populateItem($entity){
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("user_id","HTMLLabel",array(
			"text" => $entity->getUserId()
		));
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		$this->createAdd("detail_link","HTMLLink",array(
			"link" => $this->detailLink . "/" . $entity->getId()
		));
		$this->createAdd("remove_link","HTMLLink",array(
			"link" => $this->removeLink . "/" . $entity->getId(),
			"visible" => $this->session->isSuperUser() && $entity->getId() != $this->session->getId() 
		));
	}
	
}
?>