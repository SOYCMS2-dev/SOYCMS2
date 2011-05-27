<?php

class SOYCMS_MailLogic extends SOY2LogicBase{
	
	private $encoding = "ISO-2022-JP";
	private $prepared = false;
	private $sender;
	private $serverConfig = null;
	
	function getServerConfig($flag = null){
		if($this->serverConfig){ return $this->serverConfig; }
		
		$config = new SOY2Mail_ServerConfig();
		try{
			$path = SOYCMSConfigUtil::get("config_dir") . "mail.conf";
			if(file_exists($path)){
				$config->import(base64_decode(file_get_contents($path)));
			}
			
		}catch(Exception $e){
			if($flag)return null;
		}
		
		return $config;
	}
	function saveServerConfig($config){
		file_put_contents(SOYCMSConfigUtil::get("config_dir") . "mail.conf",$config->export());
	}
	
	/**
	 * 準備
	 */
	function prepareSend(){
		
		$serverConfig = $this->getServerConfig();
		
		$this->prepare();

		//SOY2Mail
		$this->sender = $serverConfig->buildSendMail();
		$this->sender->setEncoding($serverConfig->getEncoding());
		$this->sender->setSubjectEncoding($serverConfig->getEncoding());

		//FROM
		$from = $serverConfig->getFromMailAddress();
		$label = $serverConfig->getFromMailAddressName();
		$this->sender->setFrom($from,$label);
		
		//Reply-To
		if(strlen($serverConfig->getReturnMailAddress())>0){
			$this->replyTo = new SOY2Mail_MailAddress($serverConfig->getReturnMailAddress(), $serverConfig->getReturnMailAddressName(), $serverConfig->getEncoding());
		}
		
	}
	
	/**
	 * pop
	 */
	function prepare(){
		$serverConfig = $this->getServerConfig();
		
		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP
			&& $serverConfig->getReceiveServerType() != SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP
			){
				throw new Exception("invalid receive server type");
			}
			//before smtp
			$this->receive = $serverConfig->buildReceiveMail();
			$this->receive->open();
			$this->receive->close();
		}
		
		$this->prepared = true;
	}
	
	/**
	 * @param sendTo
	 * @param title
	 * @param body
	 * @param sendToName = ""
	 */
	function send($sendTo,$title,$body,$sendToName = null, $headers = array()){
		if(!$this->prepared){
			$this->prepareSend();
		}
		
		$this->sender->setHeaders(array());
		$this->sender->setRecipients(array());
			
		$this->sender->setSubject($title);
		$this->sender->setEncodedText(null);
		$this->sender->setText($body);
		
		foreach($headers as $key => $value){
			$this->sender->setHeader($key,$value);
		}
		
		if(!is_array($sendTo))$sendTo = array($sendTo,$sendToName);
		
		foreach($sendTo as $addr){
			if(is_array($addr)){
				$_sendTo = $addr[0];
				$_sendToName = $addr[1];
			}else{
				$_sendTo = $addr;
				$_sendToName = null;
			}
			
			if(!$this->isValidEmail($_sendTo)){
				continue;
			}
			
			$this->sender->addRecipient($_sendTo, $_sendToName);
		}
		
		$this->sender->send();
	}
	
	/**
	 * @return boolean
	 */
	function isValidEmail($email){
		$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
		$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
		$d3	 = '\d{1,3}';
		$ip	 = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
		$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";
		
		if(! preg_match('/'.$validEmail.'/i', $email) ) {
			return false;			
		}
		
		return true;
	}
	
	function getSender(){
		if(!$this->prepared){
			$this->prepareSend();
		}
		return $this->sender;
	}
	
	
	/* getter setter */
	
	function getEncoding() {
		return $this->encoding;
	}
	function setEncoding($encoding) {
		$this->encoding = $encoding;
	}

	function setSender($sender) {
		$this->sender = $sender;
	}
	
	function setServerConfig($config) {
		$this->serverConfig = $config;
	}
	
	
}
?>