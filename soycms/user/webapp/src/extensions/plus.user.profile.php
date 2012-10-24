<?php
/*
 *
 */
class PlusUser_ProfileField implements SOY2PluginAction{
	
	private $userId;
	private $user;
	private $profiles = array();
	
	/**
	 * 公開側表示
	 */
	function display(){
		
	}
	
	/**
	 * 公開側フォーム
	 */
	function displayForm($helper){
		
	}
	
	/**
	 * 公開側保存
	 */
	function doPostByUser(){
		
	}
	
	/**
	* @param array $profiles
	* @return <array>
	*/
	function validate($profiles){
		return array();
	}
	
	function validateForm($profiles){
		return array();
	}
	
	function doPost(){
		
	}
	
	function getForm(){
		
	}
	
	/* getter setter */
	
	

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	
	function setUser($user){
		$this->user = $user;
	}
	
	function getUser(){
		return $this->user;
	}
	
	

	public function getProfiles(){
		return $this->profiles;
	}

	public function setProfiles($profiles){
		$this->profiles = $profiles;
		return $this;
	}
}
class PlusUser_ProfileFieldAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $userId = null;
	private $user;
	private $helper = null;
	private $_errors = array();
	private $profiles = array();
	
	private $errors = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$action->setUserId($this->getUserId());
		$action->setUser($this->user);
		$action->setProfiles($this->profiles);
		
		switch($this->mode){
			case "display":	//公開側
				$action->display();
				return;
				break;
			case "user_form":	//公開側フォーム
				$action->displayForm($this->helper,$this->errors);
				return;
				break;
			case "user_post":	//公開側
				$action->doPostByUser();
				return;
				break;
			case "validate":
				$res = $action->validate($this->profiles);
				if(is_array($res)){
					$this->_errors = array_merge($this->_errors,$res);
				}
				return;
				break;
			case "post":	//設定画面でのpost
				$action->doPost();
				return;
				break;
			case "form":
				echo $action->getForm($this->helper);
				return;
				break;
			case "form_validate":
				$res = $action->validateForm($this->profiles);
				if(is_array($res)){
					$this->_errors = array_merge($this->_errors,$res);
				}
				return;
				break;
		
		}
		
	}
	
	
	/* getter setter */
	
	
	function setErrors($errors){
		$this->errors = $errors;
	}

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}

	public function getHelper(){
		return $this->helper;
	}

	public function setHelper($helper){
		$this->helper = $helper;
		return $this;
	}
	
	public function getUser(){
		return $this->user;
	}
	
	
	function getProfiles() {
		return $this->profiles;
	}
	function setProfiles($profiles) {
		$this->profiles = $profiles;
	}
	

	public function setUser($user){
		$this->user = $user;
		return $this;
	}
	
	public function getValidateErrors(){
		return $this->_errors;
	}
}

PluginManager::registerExtension("plus.user.profile","PlusUser_ProfileFieldAction");
