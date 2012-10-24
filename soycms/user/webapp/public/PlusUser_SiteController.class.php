<?php

class PlusUser_SiteController {
	
	/**
	 * @var PlusUserConfig
	 */
	private $config;
	
	function execute($page,$arguments){
		
		$_arguments = $arguments;
		
		//ログインチェック
		$session = SOY2Session::get("PlusUserSiteLoginSession");
		$mode = "page";
		
		//ログインしていない場合自動ログインを試す
		if(!$session->isLoggedIn()){
			$session->autoLogin();
		}
		
		$this->config = PlusUserConfig::getConfig();
		$mapping = $this->config->getModuleMapping();
		$moduleId = "plus_user_connector.top";	//トップページ用のモジュール
		
		//初期化
		PlusUserApplicationHelper::getInstance($this,$this->config);
		
		if(@$arguments[0] == "api"){
			array_shift($arguments); //discard first
			$mode = "api";
		}
		
		foreach($mapping as $key => $array){
			if(!$array["active"])continue;
			
			$url = $array["url"];
		
			//OK
			if(strpos(implode("/",$arguments) . "/",$url . "/") === 0){
				$moduleId = $key;
				$arguments = array_slice($arguments,count(explode("/",$url)));
				break;
			}
			
		}
		
		//execute module
		SOY2HTMLConfig::PageDir(PLUSUSER_ROOT_DIR . "public/pages/");
		SOY2HTMLConfig::TemplateDir(PLUSUSER_ROOT_DIR . "public/pages/");
		
		PluginManager::load("plus.user.common.*");
		
		PluginManager::load("plus.user.module",$moduleId);
		PluginManager::invoke("plus.user.module",array(
			"moduleId" => $moduleId,
			"page" => $page,
			"mode" => $mode,
			"config" => $this->config,
			"controller" => $this,
			"arguments" => $arguments,
			"loggedIn" => ($session && $session->isLoggedIn())
		));
		
		PluginManager::invoke("plus.user.common.finish",array(
			"controller" => $this,
			"page" => $page,
			"arguments" => $_arguments
		));
	}
	
	/**
	 * ログインしていない時にここに飛ぶ
	 */
	function jumpToLoginPage($query = null){
		$uri = $this->config->getOption("not_login_forward_uri");
		$this->jump(soycms_get_page_url(soycms_union_uri($uri)));
	}
	
	/**
	 * ログイン後のトップに飛ぶ
	 * urlはtopを付けない
	 */
	function jumpToTop(){
		$this->jump(soycms_get_page_url(soycms_union_uri($this->config->getMemberPageUrl())));
	}
	
	function jump($uri){
		SOY2PageController::redirect($uri);
		exit;
	}
	
	function jumpToModule($moduleId,$suffix = "",$query = array(),$hash = ""){
		$url = $this->config->getModulePageUrl($moduleId,$suffix,$query);
		if($hash)$url .= $hash;
		$this->jump($url);
	}


	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>