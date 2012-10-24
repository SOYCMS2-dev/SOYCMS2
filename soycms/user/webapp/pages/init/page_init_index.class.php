<?php

class page_init_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		$config = PlusUserConfig::getConfig();
		
		SOY2::cast($config,$_POST["Config"]);
		$config->setDatabaseConfig($_POST["database"]);
		
		//接続テスト
		if($config->checkConnection()){
			
			PlusUserConfig::saveConfig($config);
			
			$this->jump("init/confirm");
		}
		
		$this->error = true;
	}
	
	private $error = false;

	function page_init_index() {
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		$this->addLabel("site_url",array(
			"text" => SOYCMS_SITE_URL
		));
		
		$this->addModel("connect_error",array(
			"visible" => $this->error
		));
	}
	
	function buildForm(){
		
		$config = PlusUserConfig::getConfig();
		
		$this->addInput("mypage_url",array(
			"name" => "Config[memberPageUrl]",
			"value" => $config->getMemberPageUrl()
		));
		
		
		//DBの設定
		$database = $config->getDataBaseConfig();
		
		$this->addInput("mysql_host",array(
			"name" => "database[host]",
			"value" => @$database["host"]
		));
		
		$this->addInput("mysql_database",array(
			"name" => "database[db]",
			"value" => @$database["db"]
		));
		
		$this->addInput("mysql_user",array(
			"name" => "database[user]",
			"value" => @$database["user"]
		));
		
		$this->addInput("mysql_password",array(
			"name" => "database[password]",
			"value" => @$database["password"]
		));
		
	}
	
}
?>