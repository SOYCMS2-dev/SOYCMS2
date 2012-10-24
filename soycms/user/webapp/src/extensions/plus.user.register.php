<?php
/*
 *
 */
class PlusUser_RegisterField implements SOY2PluginAction{
	
	private $user;
	
	/**
	 * 公開側表示
	 */
	function display($groupId,$profiles = array()){
		
	}
	
	function getForm($groupId,$profiles = array()){
		
	}
	
	/**
	 * @param array $profiles
	 * @return <array>
	 */
	function validate($groupId,$profiles){
		return array();
	}
	
	function onRegister($userId,$groupId,$profiles = array()){
		
	}
	
	function setUser($user){
		$this->user = $user;
	}
	
	function getUser(){
		return $this->user;
	}
}
class PlusUser_RegisterFieldAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $userId = null;
	private $user;
	private $groupId = array();
	private $profiles = array();
	private $_errors = array();
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		$action->setUser($this->user);
		
		switch($this->mode){
			case "display":	//公開側
				echo $action->display($this->groupId,$this->profiles);
				return;
				break;
			case "form":	//公開側フォーム
				echo $action->getForm($this->groupId,$this->profiles);
				return;
				break;
			case "validate":
				$res = $action->validate($this->groupId,$this->profiles);
				if(is_array($res)){
					$this->_errors = array_merge($this->_errors,$res);
				}
				return;
				break;
			case "register":	//公開側登録時
				$action->onRegister($this->groupId,$this->userId,$this->profiles);
				return;
				break;
		}
		
	}
	
	/* getter setter */

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}

	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}

	function getProfiles() {
		return $this->profiles;
	}
	function setProfiles($profiles) {
		$this->profiles = $profiles;
	}

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	
	public function getUser(){
		return $this->user;
	}

	public function setUser($user){
		$this->user = $user;
		return $this;
	}
	
	public function getValidateErrors(){
		return $this->_errors;
	}
}

PluginManager::registerExtension("plus.user.register","PlusUser_RegisterFieldAction");
