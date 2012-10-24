<?php

class page_config_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		/* @var $helper PlusUserConfigHelper */
		$helper = SOY2Logic::createInstance("PlusUserConfigHelper");
		$helper->syncConfig();
		
		//初期テンプレート
		if(isset($_POST["init"])){
			$helper->saveDefaultTemplates();
		}
		
		$config = PlusUserConfig::getConfig();
		
		if(isset($_POST["Module"])){
			
			PluginManager::load("plus.user.module");
			$list = PluginManager::invoke("plus.user.module")->getList();
		
			$mappings = $config->getModuleMapping();
			foreach($_POST["Module"] as $key => $value){
				if(!isset($mappings[$key])){
					$mappings[$key] = array(
						"active" => 0
					);
				}
				
				if(empty($value["url"])){
					$_POST["Module"][$key]["url"] = $key;
				}
				
				$_POST["Module"][$key]["login"] = $list[$key]["login"];
				
				if(@$value["active"] == 1 && $mappings[$key]["active"] != 1){
					PluginManager::invoke("plus.user.module",array(
						"moduleId" => $key,
						"mode" => "active"
					));
				}
			}
			
			
			$config->setModuleMapping($_POST["Module"]);
		}
		if(isset($_POST["config"])){
			SOY2::cast($config,$_POST["config"]);
			PlusUserConfig::saveConfig($config);
		}
		
		
		$this->jump("config?updated");
		
	}

	function page_config_index() {
		WebPage::WebPage();
		$this->addForm("form");
		
		$config = PlusUserConfig::getConfig();
		
		$this->addLabel("site_url",array(
			"text" => soycms_get_page_url(""),
		));
		
		$this->addLabel("member_page_url",array(
			"text" => soycms_get_page_url($config->getMemberPageUrl()),
		));
		
		
		
		$this->createAdd("module_list","_class.list.ModuleList",array(
			"list" => $config->getModuleMapping(),
			"memberPageUrl" => soycms_get_page_url($config->getMemberPageUrl()),
			"siteUrl" =>  soycms_get_page_url(""),
		));
		
		$this->addInput("not_login_uri",array(
			"name" => "config[options][not_login_forward_uri]",
			"value" => $config->getOption("not_login_forward_uri","/mypage/login")
		));
		$this->addInput("logout_uri",array(
			"name" => "config[options][logout_forward_uri]",
			"value" => $config->getOption("logout_forward_uri","")
		));
		
		$this->addTextArea("login_url_list",array(
			"name" => "config[options][login_url_list]",
			"value" => $config->getOption("login_url_list","")
		));
	}
}
?>