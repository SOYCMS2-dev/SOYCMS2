<?php

class page_init_confirm extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["back"])){
			$this->jump("/init");
		}
		
		//初期化実行
		$sql = PLUSUSER_ROOT_DIR . "src/sql/init.sql";
		$pdo = $this->config->getConnection();
		$sql = file_get_contents($sql);
		$sqls = explode(";",$sql);

		foreach($sqls as $sql){
			try{
				if(empty($sql))continue;
				$pdo->exec($sql);
			}catch(Exception $e){
				
			}
		}
		
		//フラグの書き込み
		SOYCMS_DataSets::put("plus.user.inited",date("Y-m-d H:i:s"));
		
		$this->jump("/init/complete");
	}
	
	private $config;
	
	function init(){
		$this->config = PlusUserConfig::getConfig();
		
		if(!$this->config->checkConnection()){
			$this->jump("/init");
		}
	}

	function page_init_confirm() {
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		
		$res = SOYCMSConfigUtil::get("user_conf");
		$path = SOYCMSConfigUtil::get("config_dir") . "plus_user.conf.php";
		if($res)$path = $res;
		
		$this->addLabel("config_file_path",array(
			"text" => $path
		));
		
	}
	
	function buildForm(){
		
	}
	
}
?>