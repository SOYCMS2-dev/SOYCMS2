<?php

class MailServerConfigForm extends HTMLForm{
	
	private $config;
	
	function execute() {
		$serverConfig = $this->getConfig();
		
		$this->createAdd("send_server_type_sendmail","HTMLCheckBox",array(
			"elementId" => "send_server_type_sendmail",
			"name" => "ServerConfig[sendServerType]",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL),
			"onclick" => 'toggleSMTP()'
		));
		$this->createAdd("send_server_type_smtp","HTMLCheckBox",array(
			"elementId" => "send_server_type_smtp",
			"name" => "ServerConfig[sendServerType]",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SMTP,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'toggleSMTP()'
		));
		
		
		$this->createAdd("is_use_pop_before_smtp","HTMLCheckBox",array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "ServerConfig[isUsePopBeforeSMTP]",
			"value" => 1,
			"selected" => $serverConfig->getIsUsePopBeforeSMTP(), 
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
			"onclick" => 'togglePOPIMAPSetting();'
		));
		
		$this->createAdd("is_use_smtp_auth","HTMLCheckBox",array(
			"elementId" => "is_use_smtp_auth",
			"name" => "ServerConfig[isUseSMTPAuth]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $serverConfig->getIsUseSMTPAuth(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
			"onclick" => 'toggleSMTPAUTHSetting();'
		));
		
		$this->createAdd("send_server_address","HTMLInput",array(
			"id" => "send_server_address",
			"name" => "ServerConfig[sendServerAddress]",
			"value" => $serverConfig->getSendServerAddress(), 
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
		));
		$this->createAdd("send_server_port","HTMLInput",array(
			"id" => "send_server_port",
			"name" => "ServerConfig[sendServerPort]",
			"value" => $serverConfig->getSendServerPort(), 
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
		));
		
		
		$this->createAdd("send_server_user","HTMLInput",array(
			"id" => "send_server_user",
			"name" => "ServerConfig[sendServerUser]",
			"value" => $serverConfig->getSendServerUser(), 
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(), 
		));
		$this->createAdd("send_server_password","HTMLInput",array(
			"id" => "send_server_password",
			"name" => "ServerConfig[sendServerPassword]",
			"value" => $serverConfig->getSendServerPassword(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(), 
			"attr:autocomplete" => "off",
		));
		
		$this->createAdd("is_use_ssl_send_server","HTMLCheckBox",array(
			"elementId" => "is_use_ssl_send_server",
			"name" => "ServerConfig[isUseSSLSendServer]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
			"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
			"onclick" => 'changeSendPort();'
		));
		
		/* 受信設定 */
		$this->createAdd("receive_server_type_pop","HTMLCheckBox",array(
			"elementId" => "receive_server_type_pop",
			"name" => "ServerConfig[receiveServerType]",
			"value" => SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP,
			"selected" => ($serverConfig->getReceiveServerType() == SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(), 
			"onclick" => 'changeReceivePort();'
		));
		
		$this->createAdd("receive_server_type_imap","HTMLCheckBox",array(
			"elementId" => "receive_server_type_imap",
			"name" => "ServerConfig[receiveServerType]",
			"value" => SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP,
			"selected" => ($serverConfig->getReceiveServerType() == SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP() OR !$this->isIMAPEnabled(), 
			"onclick" => 'changeReceivePort();'
		));
		
		$this->createAdd("receive_server_address","HTMLInput",array(
			"id" => "receive_server_address",
			"name" => "ServerConfig[receiveServerAddress]",
			"value" => $serverConfig->getReceiveServerAddress(), 
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(), 
		));
		
		$this->createAdd("receive_server_user","HTMLInput",array(
			"id" => "receive_server_user",
			"name" => "ServerConfig[receiveServerUser]",
			"value" => $serverConfig->getReceiveServerUser(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(), 
		));
		
		$this->createAdd("receive_server_password","HTMLInput",array(
			"id" => "receive_server_password",
			"name" => "ServerConfig[receiveServerPassword]",
			"value" => $serverConfig->getReceiveServerPassword(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(), 
			"attr:autocomplete" => "off",
		));
		
		$this->createAdd("receive_server_port","HTMLInput",array(
			"id" => "receive_server_port",
			"name" => "ServerConfig[receiveServerPort]",
			"value" => $serverConfig->getReceiveServerPort(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(), 
		));
		
		$this->createAdd("is_use_ssl_receive_server","HTMLCheckBox",array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "ServerConfig[isUseSSLReceiveServer]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLReceiveServer(),
			"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP), 
			"onclick" => 'changeReceivePort();'
		));
		
		/* SSL */
		$this->createAdd("is_ssl_enabled", "HTMLHidden", array(
			"id"	=> "is_ssl_enabled",
			"value" => (int) $this->isSSLEnabled()
		));
		$this->createAdd("ssl_disabled", "HTMLModel", array(
			"visible" => !$this->isSSLEnabled()
		));
		/* IMAP */
		$this->createAdd("is_imap_enabled", "HTMLHidden", array(
			"id"	=> "is_imap_enabled",
			"value" => (int) $this->isIMAPEnabled()
		));
		$this->createAdd("imap_disabled", "HTMLModel", array(
			"visible" => $this->isIMAPEnabled()
		));
		
		/* メール設定 */
		$this->createAdd("administrator_address","HTMLInput",array(
			"name" => "ServerConfig[fromMailAddress]",
			"value" => $serverConfig->getFromMailAddress()
		));
		$this->createAdd("administrator_name","HTMLInput",array(
			"name" => "ServerConfig[fromMailAddressName]",
			"value" => $serverConfig->getFromMailAddressName() 
		));
		$this->createAdd("return_address","HTMLInput",array(
			"name" => "ServerConfig[returnMailAddress]",
			"value" => $serverConfig->getReturnMailAddress() 
		));
		$this->createAdd("return_name","HTMLInput",array(
			"name" => "ServerConfig[returnMailAddressName]",
			"value" => $serverConfig->getReturnMailAddressName() 
		));
		
		$this->createAdd("mail_encoding","HTMLSelect",array(
			"name" => "ServerConfig[encoding]",
			"selected" => $serverConfig->getEncoding() ,
			"options" => array(
				"ISO-2022-JP" => "JIS (ISO-2022-JP)",
				"UTF-8" => "UTF-8",
			)
		));
		
		parent::execute();
	}
	
	/**
	 * SSLが使用可能かを返す
	 * @return Boolean
	 */
	function isSSLEnabled(){
		return function_exists("openssl_open");
	}
	
	/**
	 * IMAPが使用可能かを返す
	 */
	function isIMAPEnabled(){
		return function_exists("imap_open");
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>