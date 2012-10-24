<?php
/*
 * Created on 2011/01/16
 * Block Extensions
 */
class PlusUser_Module implements SOY2PluginAction{
	
	private $moduleId;
	private $config;
	private $controller;
	
	/**
	 * 名前
	 */
	function getName(){
		return get_class($this);
	}
	
	/**
	 * 説明
	 */
	function getDescription(){
		return "";
	}
	
	/**
	 * 設定画面
	 */
	function getConfigure($page){
		return "";
	}
	
	/**
	 * 設定画面からのポスト
	 */
	function postConfigure($page){
		
	}
	
	/**
	 * 公開側画面を出力
	 */
	function display($page,$arguments = array()){
		
	}
	
	/**
	 * apiを呼ばれる
	 */
	function call($arguments,$result){
		return null;
	}
	
	/**
	 * ログインが必要かどうか
	 */
	function requireLogin(){
		return true;
	}
	
	/**
	 * 有効時に動作
	 */
	function onActive(){
		
	}
	
	/**
	 * 通知
	 * @return array<key => title>
	 */
	function getNotifications(){
		return array();
	}
	
	function getUri(){
		return $this->getModuleId();
	}

	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
	
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getController() {
		return $this->controller;
	}
	function setController($controller) {
		$this->controller = $controller;
	}
}
class PlusUser_ModuleAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $list = array();
	private $page = null;
	private $moduleId;
	private $module;
	private $arguments = array();
	private $controller = null;
	private $config = array();
	private $loggedIn = false;
	
	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		SOY2::cast($action,$this);
		$action->setModuleId($moduleId);
		
		switch($this->mode){
			case "page":	//公開側
				if($moduleId == $this->moduleId){
					if($action->requireLogin() === true && !$this->loggedIn){
						$this->controller->jumpToLoginPage();
					}
					$action->display($this->page,$this->arguments);
					return;
				}
				break;
			case "api":
				if($moduleId == $this->moduleId){
					$res = array(
						"api" => $this->moduleId,
						"error" => 0,
						"error_message" => "",
						"result" => null
					);
					if($action->requireLogin() && !$this->loggedIn){
						$res["error"] = 1;
						$res["error_message"] = "require login";
					}else{
						$_res = $action->call($this->arguments,$res);
						if($_res){
							list($res,$result) = $_res;
							$res["result"] = $result;
						}
					}
					
					echo json_encode($res);
					exit;
					return;
				}
				break;
			case "form":	//設定画面
				if($moduleId == $this->moduleId){
					SOY2::cast($action,$this->config);
					
					echo $action->getConfigure($this->page);
					return;
				}
				break;
			case "active":
				if($moduleId == $this->moduleId){
					SOY2::cast($action,$this->config);
					$action->onActive();
					return;
				}
				break;
			case "load":
				if($moduleId == $this->moduleId){
					$this->module = $action;
				}
				break;
			case "notify_config":
				$this->list = array_merge($this->list,$action->getNotifications());
				break;
			default:
			case "list":
				$this->list[$moduleId] = array(
					"name" => $action->getName(),
					"description" => $action->getDescription(),
					"defaultUrl" => $action->getUri(),
					"login"=> ($action->requireLogin()) ? 1 : 0
				);
				break;
		
		}
		
	}
	
	function invokeExecute($block,$htmlObj){
		if(!$this->module)return true;
		return $this->module->onExecute($block,$htmlObj);
	}
	
	function getEntries($from,$to){
		if(!$this->module)return array();
		return $this->module->getEntries($from,$to);
	}
	
	/* getter setter */
	

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getList() {
		return $this->list;
	}
	function setList($list) {
		$this->list = $list;
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}

	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
	function getModule() {
		return $this->module;
	}
	function setModule($module) {
		$this->module = $module;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getController() {
		return $this->controller;
	}
	function setController($controller) {
		$this->controller = $controller;
	}

	function getLoggedIn() {
		return $this->loggedIn;
	}
	function setLoggedIn($loggedIn) {
		$this->loggedIn = $loggedIn;
	}

	function getArguments() {
		return $this->arguments;
	}
	function setArguments($arguments) {
		$this->arguments = $arguments;
	}
}

PluginManager::registerExtension("plus.user.module","PlusUser_ModuleAction");
