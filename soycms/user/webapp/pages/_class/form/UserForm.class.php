<?php

class UserForm extends HTMLForm{
	
	private $user;

	function execute(){
		$user = $this->getUser();
		
		$this->addInput("login_id",array(
			"name" => "User[loginId]",
			"value" => $user->getLoginId()
		));
		
		$this->addInput("password",array(
			"name" => "User[password]",
			"value" => $user->getPassword()
		));
		
		$this->addInput("user_name",array(
			"name" => "User[name]",
			"value" => $user->getName()
		));
		
		$this->addInput("mail_address",array(
			"name" => "User[mailAddress]",
			"value" => $user->getMailAddress()
		));
		
		$this->addSelect("user_status",array(
			"name" => "User[status]",
			"selected" => $user->getStatus(),
			"options" => array(
				-1 => "削除",
				0 => "仮登録",
				1 => "登録済み"
			)
		));
		
		parent::execute();
	}

	function getUser() {
		return $this->user;
	}
	function setUser($user) {
		$this->user = $user;
	}
}
?>