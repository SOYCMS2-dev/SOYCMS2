<?php
/**
 * @title 管理者詳細
 */
class page_user_profile extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["User"]) && soy2_check_token()){
			SOY2::cast($this->user,$_POST["User"]);
			$this->user->save();
			
			//
			$session = SOY2Session::get("base.session.UserLoginSession");
			$session->setName($this->user->getName());
			
			$this->jump("/user/profile?updated");
		}
		
		if(isset($_POST["update_password"])){
			$array = $_POST["password"];
			/* $old = $array["old"]; */
			$new = $array["new"];
			$confirm = $array["confirm"];
		
			if( ($new == $confirm) && strlen($new) >= 4
				/* && $this->user->checkPassword($old) */){
				
				$this->user->setPassword($this->user->hashPassword($new));
				$this->user->save();
				
				$this->jump("/user/profile?updated");	
			}
			
			$this->password_error = true;
		}
		
	}
	
	private $user;
	private $session;
	private $password_error = false; 
	
	function init(){
		try{
			$this->session = SOY2Session::get("base.session.UserLoginSession");
			$this->user = SOY2DAO::find("Plus_User",$this->session->getId());
		}catch(Exception $e){
			$this->jump("/user");
		}
	}

	function page_user_profile($args){
		WebPage::WebPage();
		
		$this->addLabel("user_name_text",array(
			"text" => $this->user->getName()
		));
		
		$this->createAdd("detail_form","_class.form.UserForm",array(
			"user" => $this->user
		));
		
		$this->buildForm();
	
	}
	
	function buildForm(){
		$this->addForm("password_form");
		
		$array = (isset($_POST["password"])) ? @$_POST["password"] : array();
		
		$this->addModel("passwrod_error",array("visible" => $this->password_error));
		
		$this->addInput("old_password",array(
			"name" => "password[old]",
			"value" => @$array["old"]
		));
		
		$this->addInput("new_password",array(
			"name" => "password[new]",
			"value" => @$array["new"]
		));
		
		$this->addInput("new_password_confirm",array(
			"name" => "password[confirm]",
			"value" => @$array["confirm"]
		));
		
	}
}