<?php
/**
 * @title メールサーバの設定
 */
class page_config_mail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["ServerConfig"])){
			$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
			$serverConfig = $logic->getServerConfig();
			SOY2::cast($serverConfig,$_POST["ServerConfig"]);
			$logic->saveServerConfig($serverConfig);
			
			if(isset($_POST["test_mail"])){
				
				$logic->send(
					$serverConfig->getFromMailAddress(),
					"Test mail from SOYCMS",
					"This is test mail from SOYCMS\n" .
					soycms_create_link("/config#tab2",true)
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
			
			$this->jump("/config#tab2");
		}
	}

	function page_config_mail(){
		WebPage::WebPage();
	}
	
	function execute(){
		
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		$serverConfig = $logic->getServerConfig();
		$this->createAdd("form","_class.form.MailServerConfigForm",array(
			"config" => $serverConfig
		));
	}
	
	function getLayout(){
		return "blank.php";
	}
}