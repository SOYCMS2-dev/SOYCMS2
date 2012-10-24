<?php
/*
 * 
 */
class PlusUser_WithdrawAction implements SOY2PluginAction{
	
	/**
	 * 退会する時に呼ばれます
	 */
	function onWithdraw($userId,$user){
		
	}
}
class PlusUser_WithdrawActionDelegater implements SOY2PluginDelegateAction{
	
	private $userId = null;
	private $user = null;
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$action->onWithdraw($this->userId,$this->user);
		
	}
	
	/* getter setter */

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getUser(){
		return $this->user;
	}
	function setUser($user){
		$this->user = $user;
	}
}

PluginManager::registerExtension("plus.user.withdraw","PlusUser_WithdrawActionDelegater");
