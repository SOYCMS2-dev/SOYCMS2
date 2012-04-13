<?php

class UserLoginSession extends SOY2Session{

	private $loggedIn = false;
	private $id;
	private $userId;
	private $name;
	private $level = 1;
	private $theme = "gray";
	
	function wakeup(){
		define("SOYCMS_LOGIN_USER_NAME",	$this->name);
		define("SOYCMS_LOGIN_USER_ID",		$this->id);
	}
	
	/**
	 * ログインしているかどうか
	 * @return boolean
	 */
	function isLoggedIn(){
		return $this->loggedIn;
	}
	
	function logout(){
		$this->loggedIn = false;
		$this->id = null;
		$this->userId = null;
		$this->name = null;
	}
	
	/**
	 * ログイン実行
	 * @param userId
	 * @param password
	 * @param options(option)
	 */
	function login($userId,$password){
		try{
			$user = SOY2DAO::find("SOYCMS_User",array("userId" => $userId));
			
			if($user->checkPassword($password)){
				$this->doLogin($user);
				
				return true;
			}
		}catch(Exception $e){
			
		}
		return false;
	}
	
	function doLogin(SOYCMS_User $user){
		$this->id = $user->getId();
		$this->userId = $user->getUserId();
		$this->name = $user->getName();
		$this->setLoggedIn(true);
		$this->setLevel($user->getLevel());
	}
	
	
	
	function isSuperUser(){
		return ($this->level == 0);
	}
	
	
	/* getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}

	function getLoggedIn() {
		return $this->loggedIn;
	}
	function setLoggedIn($loggedIn) {
		$this->loggedIn = $loggedIn;
	}

	function getLevel() {
		return $this->level;
	}
	function setLevel($level) {
		$this->level = $level;
	}

	function getTheme() {
		return $this->theme;
	}
	function setTheme($theme) {
		$this->theme = $theme;
	}
}
?>