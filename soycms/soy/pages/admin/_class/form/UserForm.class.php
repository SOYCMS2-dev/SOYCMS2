<?php

class UserForm extends HTMLForm{
	
	private $user;

	function execute(){
		$user = $this->getUser();
		
		$this->addInput("user_id",array(
			"name" => "User[userId]",
			"value" => $user->getUserId()
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
		
		/* config */
		$config = $user->getConfigArray();
		
		$this->addInput("config_link_text",array(
			"name" => "User[config][link_text]",
			"value" => @$config["link_text"]
		));
		
		$this->addInput("config_link_url",array(
			"name" => "User[config][link_url]",
			"value" => @$config["link_url"]
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