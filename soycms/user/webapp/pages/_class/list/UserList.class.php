<?php

class UserList extends HTMLList{
	
	private $detailLink;
	private $removeLink;
	private $session;
	private $groups = array();
	
	function init(){
		$this->detailLink = SOY2FancyURIController::createLink("/user/detail");
		$this->removeLink = SOY2FancyURIController::createLink("/user/remove");
		$this->session = SOY2Session::get("base.session.UserLoginSession");;
		$groups = SOY2DAO::find("Plus_Group");
		
		foreach($groups as $group){
			$groupId = $group->getGroupId();
			$this->groups[$groupId] = $group;
		}
		
	}
	
	function getGroupText($entity){
		$res = array();
		$ids = explode(",",$entity->getGroupIds());
		foreach($ids as $key){
			if(!isset($this->groups[$key]))continue;
			$res[] = $this->groups[$key]->getName();
		}

		if(empty($res)){
			return "-";
		}
		return implode(",",$res);
	}
	
	function populateItem($entity){
		/* @var $entity Plus_User */
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("id_text","HTMLLabel",array(
			"text" => (function_exists("plus_user_print_id")) ? plus_user_print_id($entity->getId()) : $entity->getId()
		));
		
		$this->createAdd("login_id","HTMLLabel",array(
			"text" => $entity->getLoginId()
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
		
		$this->addLabel("group_text",array(
			"text" => $this->getGroupText($entity)
		));
		
		$this->addLabel("update_date",array("text" => date("Y-m-d",$entity->getCreateDate())));
		$this->addLabel("create_date",array("text" => date("Y-m-d",$entity->getUpdateDate())));
	}
	
}
?>