<?php

class page_config extends PlusUserWebPageBase{
	
	private $user;
	private $mode = "top";
	
	function doPost(){
		
		if(isset($_POST["notify_mailaddress"])){
			$user = SOY2DAO::find("Plus_User",$this->getSession()->getId());
			$user->setMailAddress($_POST["notify_mailaddress"]);
			$user->save();
		}
		
		if(isset($_POST["mobile_mail_address"])){
			Plus_UserConfig::put($this->getSession()->getId(), "mobile_mail_address", $_POST["mobile_mail_address"]);
		}
		
		if(isset($_POST["NotifyConfig"])){
			Plus_UserConfig::put($this->getSession()->getId(),"notify_config",$_POST["NotifyConfig"]);
		}
		
		PlusUserApplicationHelper::getController()->jumpToModule("plus_user_connector.config","notify",array(
			"updated" => 1
		));
		
	}
	
	function init(){
		$this->user = SOY2DAO::find("Plus_User",$this->getSession()->getId());
		$this->setConfig(PlusUserConfig::getConfig());
		PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.config","設定");
	}

	function page_config($args) {
		if(count($args) > 0)$this->mode = $args[0];
		WebPage::WebPage();
	}
	
	function buildPage(){
		foreach(array("top","notify") as $value){
			$this->addModel("mode_" . $value,array(
				"visible" => $this->mode == $value
			));
		}
		
		switch($this->mode){
			case "notify":
				PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.config","通知設定","notify");
				break;
		}
		
		$this->addLabel("user_notify_mailaddress",array(
			"text" => $this->user->getMailAddress()
		));
		
		$this->addInput("user_notify_mailaddress_input",array(
			"name" => "notify_mailaddress",
			"value" => $this->user->getMailAddress()
		));
		
		$mobileAddr = $this->user->getConfigValue("mobile_mail_address","");
		$this->addLabel("user_notify_mobile_mailaddress",array(
			"text" => (empty($mobileAddr)) ? "-" : $mobileAddr
		));
		
		$this->addInput("user_notify_mobile_mailaddress_input",array(
			"name" => "mobile_mail_address",
			"value" => $mobileAddr
		));
	}
	
	function buildForm(){
		
		$this->addForm("config_form");
		
		$this->addLink("notify_config_link",array(
			"link" => $this->getConfig()->getModulePageUrl("plus_user_connector.config","notify")
		));
		
		//全モジュールの通知を確認する
		PluginManager::load("plus.user.module");
		$delegate = PluginManager::invoke("plus.user.module",array(
				"mode" => "notify_config",
				"config" => $this->getConfig()
		));
		$list = $delegate->getList();
		
		$this->createAdd("notify_list","NotifyConfigList",array(
			"list" => $list,
			"childSoy2Prefix" => "cms",
			"values" => Plus_UserConfig::get($this->getSession()->getId(),"notify_config",array("on_message"))
		));
	}
	
}

class NotifyConfigList extends HTMLList{
	
	private $values = array();
	
	function populateItem($entity,$key){
		$this->addLabel("notify_title",array(
			"text" => $entity
		));
		
		$this->addCheckbox("notify_checkbox",array(
			"name" => "NotifyConfig[$key]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (isset($this->values[$key])) ? ($this->values[$key] == 1) : 1
		));
		
		$this->addCheckbox("notify_mobile_checkbox",array(
			"name" => "NotifyConfig[{$key}.mobile]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (isset($this->values[$key . ".mobile"])) ? ($this->values[$key . ".mobile"] == 1) : 0 
 		));
	}
	

	public function getValues(){
		return $this->values;
	}

	public function setValues($values){
		if(!is_array($values))$values = array();
		$this->values = $values;
		return $this;
	}
}
?>