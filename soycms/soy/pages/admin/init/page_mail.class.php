<?php
SOY2::imports("_class.form.*",SOYCMS_ROOT_DIR . "soy/pages/admin/");

/**
 * @title メールサーバの設定
 */
class page_mail extends WebPage{
	
	function doPost(){
		
		if(isset($_POST["ServerConfig"])){
			$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
			$serverConfig = $logic->getServerConfig();
			SOY2::cast($serverConfig,$_POST["ServerConfig"]);
			$logic->setServerConfig($serverConfig);	//一時的に書きこむ
			
			$_SESSION["soycms_init_server"] = $serverConfig;
			
			if(isset($_POST["test_mail"])){
				
				$logic->send(
					$serverConfig->getFromMailAddress(),
					"Test mail from SOYCMS",
					"This is test mail from SOYCMS"
				);
				
				if(isset($_POST["test_mailaddress"])){
					$logic->send(
						$_POST["test_mailaddress"],
						"Test mail from SOYCMS",
						"This is test mail from SOYCMS\n" .
						soycms_create_link("/config#tab2",true)
					);
				}
			}
		}
		
		SOY2PageController::jump("mail");
	}
	
	function init(){
		@session_start();
	}

	function page_mail(){
		WebPage::WebPage();
		
		$this->execute();
		
	}
	
	function execute(){
		
		//セッションから取得
		$serverConfig = @$_SESSION["soycms_init_server"];
		
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		if(!$serverConfig){
			$serverConfig = $logic->getServerConfig();
		}
		
		$this->createAdd("form","MailServerConfigForm",array(
			"config" => $serverConfig
		));
	}
	
	function getTemplateFilePath(){
		return SOY2HTMLConfig::TemplateDir() . "init/page_mail.html";
	}
	
	function getLayout(){
		return "layer.php";
	}
}