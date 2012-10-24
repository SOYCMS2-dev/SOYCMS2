<?php
class page_password_remind extends PlusUserWebPageBase{
	
	function doPost(){
		
		//送信
		if(isset($_POST["send"]) || isset($_POST["resend"])){
			$this->mailaddress = $mailaddress = $_POST["mailaddress"];
			
			if($user = $this->checkMailAddress($mailaddress)){
				$this->sendRemindMail($user);
				$this->jump("send",array("mailaddress" => $this->mailaddress));
			}
			$this->errors["mailaddress"] = true;
		}
		
		//パスワードのリセット
		if(isset($_POST["reset_password"])){
			$new_password = @$_POST["password"];
			$confirm = @$_POST["password_confirm"];
			if(strlen($new_password) > 1){
				if($new_password === $confirm){
					$this->user->setPassword($this->user->hashPassword($new_password));
					$this->user->save();
					
					$this->token->delete();
					$this->jump("complete",array("mailaddress" => $this->mailaddress));
				}
				$this->errors["password_confirm"] = true;
			}else{
				$this->errors["password"] = true;
			}
			
		}
		
	}
	
	private $mailaddress;
	private $password;
	private $password_remind;
	
	/**
	 * @var Plus_User
	 */
	private $user = null;
	private $token;
	private $errors = array();
	private $config = null;
	private $mode = "form";
	
	function init(){
		$session = plus_user_get_session();
		if($session->isLoggedIn()){
			PlusUserApplicationHelper::getController()->jumpToTop();
		}
		
		$this->config = PlusUserConfig::getConfig();
		
		if(isset($_GET["mailaddress"])){
			$this->mailaddress = $_GET["mailaddress"];
		}
		
		if(isset($_GET["mode"])){
			$this->mode = $_GET["mode"];
		}
		
		if(isset($_GET["token"])){
			try{
				$token = SOY2DAO::find("Plus_UserToken",array("token" => $_GET["token"]));
				$this->mode = "reset";
				$this->token = $token;
				
				$user = SOY2DAO::find("Plus_User",$token->getUserId());
				$this->mailaddress = $user->getMailAddress();
				$this->user = $user;
				
			}catch(Exception $e){
				$this->mode = "error";
			}
		}
		
		PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.profile","ログインパスワードの変更");
	}

	function page_password_remind($args) {
		if(count($args) > 0)$this->mode = $args[0];
		WebPage::WebPage();
	}
	
	function buildPage(){
		
		foreach(array("form","send","reset","complete","error") as $mode){
			$this->addModel("mode_" . $mode,array(
				"visible" => $mode == $this->mode
			));
		}
		
		//form
		$this->addForm("password_remind_form");
		$this->addModel("password_remind_mailaddress_error",array("visible" => @$this->errors["mailaddress"]));
		$this->addInput("password_remind_mailaddress",array(
			"name" => "mailaddress",
			"value" => $this->mailaddress
		));
		
		//send
		$this->addForm("password_resend_form");
		$this->addInput("password_remind_resend_mailaddress",array(
			"name" => "mailaddress",
			"value" => $this->mailaddress
		));
		$this->addLabel("password_remind_target_mailaddress",array(
			"text" => $this->mailaddress
		));
		
		//reset
		$this->addForm("password_reset_form");
		$this->addInput("password_remind_password",array(
			"name" => "password",
			"value" => $this->password
		));
		$this->addInput("password_remind_password_confirm",array(
			"name" => "password_confirm",
			"value" => "",
		));
		$this->addModel("password_remind_password_error",array(
			"visible" => @$this->errors["password"]
		));
		$this->addModel("password_remind_password_confirm_error",array(
			"visible" => @$this->errors["password_confirm"]
		));
	}
	
	/**
	 * @return Plus_User
	 * @param string $mailaddress
	 */
	function checkMailAddress($addr){
		
		try{
			//UserIdをメールから取得
			$userDAO = SOY2DAOFactory::create("Plus_UserDAO");
			$user = $userDAO->getByMailAddress($addr);
			if($user->getStatus() > 0){
				return $user;
			}
			
		}catch(Exception $e){
			
		}
		
		return false;
	}
	
	/**
	 * リマインドメールの送信
	 * @param Plus_User $user
	 */
	function sendRemindMail($user){
		
		//Tokenを作成
		$token = Plus_UserToken::generateToken($user->getId());
		
		$url = $this->config->getModulePageUrl("plus_user_connector.password_remind","",array(
			"token" => $token->getToken()
		));
		
		$title = SOYCMS_DataSets::get("plus_user_connector.password_remind.title","パスワードリマインドメール");
		$body = SOYCMS_DataSets::get("plus_user_connector.password_remind.body",
						"下記URLより新しいパスワードを設定して下さい。\n\n" .
						"  #URL#\n\n" .
						"上記URLは#YEAR#年#MONTH#月#DATE#日#HOUR#:#MINUTE#:#SECOND#以降は利用することが出来ません"
		);
		
		$body = str_replace('#USERNAME#',$user->getName(),$body);
		$body = str_replace('#URL#',$url,$body);
		$body = str_replace('#YEAR#',date("Y",$token->getLimit()),$body);
		$body = str_replace('#MONTH#',date("m",$token->getLimit()),$body);
		$body = str_replace('#DATE#',date("d",$token->getLimit()),$body);
		$body = str_replace('#HOUR#',date("H",$token->getLimit()),$body);
		$body = str_replace('#SECOND#',date("i",$token->getLimit()),$body);
		$body = str_replace('#MINUTE#',date("s",$token->getLimit()),$body);
			
		//MailLogicを作成
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		$logic->send(
			$user->getMailAddress(),
			$title,
			$body
		);
		
	}
	
	/**
	 * リダイレクト
	 * @param array $query
	 */
	function jump($suffix = "",$query = array()){
		PlusUserApplicationHelper::getController()->jumpToModule("plus_user_connector.password_remind",$suffix,$query);
		exit;
	}
	
	/* getter setter */

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>