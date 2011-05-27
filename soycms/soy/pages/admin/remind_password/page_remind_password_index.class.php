<?php
/**
 * @title ログイン
 */
class page_remind_password_index extends WebPage{
	
	function doPost(){
		if(isset($_POST["mailaddress"])){
			
			$addr = $_POST["mailaddress"];
			$this->sendTokenMail($addr);
			
			SOY2PageController::redirect("./remind_password?sended&addr=" . $addr);
		}
		
		if(isset($_POST["new_password"])){
			
			if($_POST["token"] && $_POST["new_password"] == $_POST["new_password_confirm"] && strlen($_POST["new_password"])>3){
				$this->user->setPassword($this->user->hashPassword($_POST["new_password"]));
				$this->user->save();
				
				//remove token
				$this->token->delete();
				
				SOY2PageController::redirect("./remind_password?reset");
			}
			
		}
		
		DisplayPlugin::visible("errors");
	}

	function page_remind_password_index(){
		DisplayPlugin::hide("errors");
		
		WebPage::WebPage();
		
		$this->buildPages();
		$this->buildForm();
	}
	
	private $user;
	private $token;
	
	function init(){
		$this->user = new SOYCMS_User();
		
		//Tokenが指定されていた時はTokenからUserを取得
		if(isset($_REQUEST["token"])){
			try{
				$this->token = SOY2DAO::find("SOYCMS_UserToken",array("token" => $_REQUEST["token"]));
				$this->user = SOY2DAO::find("SOYCMS_User",array("userId" => $this->token->getUserId()));
				
				if($this->token->getLimit() < time()){
					$this->token->delete();
					throw new Exception("");
				}
				
			}catch(Exception $e){
				SOY2PageController::jump("/remind_password/error");
			}
		}
		
	}
	
	function buildPages(){
		
		$this->addLabel("maiil_addr",array("text" => @$_GET["addr"]));
		
		$this->addModel("form_window",array(
			"visible" => 
				(!isset($_GET["sended"]) && !isset($_GET["token"]) && !isset($_GET["reset"]))
		));
		
		$this->addModel("confirm_window",array(
			"visible" => (isset($_GET["sended"]))
		));
		
		$this->addModel("reset_password",array(
			"visible" => (isset($_GET["token"]))
		));
		
		$this->addModel("password_set",array(
			"visible" => (isset($_GET["reset"]))
		));
	}
	
	function buildForm(){
		$this->addForm("confirm_form");
		$this->addInput("mail_addr",array(
			"name" => "mailaddress",
			"value" => ""
		));	
		
		$this->addForm("reset_form");
		
		$this->addLabel("your_id",array(
			"text" => $this->user->getUserId()
		));
		
		$this->addInput("new_password",array(
			"name" => "new_password",
			"value" => ""
		));
		
		$this->addInput("new_password_confirm",array(
			"name" => "new_password_confirm",
			"value" => ""
		));
		
		$this->addInput("token",array(
			"name" => "token",
			"value" => ($this->token) ? $this->token->getToken() : ""
		));
	}
	
	function getLayout(){
		return "login.php";
	}
	
	function sendTokenMail($addr){
		
		try{
			//UserIdをメールから取得
			$userDAO = SOY2DAOFactory::create("SOYCMS_UserDAO");
			$user = $userDAO->getByMailAddress($addr);
			
			//Tokenを作成
			$token = SOYCMS_UserToken::generateToken($user->getUserId());
			
			//MailLogicを作成
			$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
			$logic->send(
				$user->getMailAddress(),
				"Reset Your Password [".$_SERVER["HTTP_HOST"]."]",
				soycms_create_link("/remind_password?token=" . $token->getToken() ,true) . "\n" .
				"※本URLは".(date("n月j日 H時i分",$token->getLimit()))."を過ぎると無効となります"
			);
			
			
		}catch(Exception $e){
			
		}
		
		
		
		
	}
}