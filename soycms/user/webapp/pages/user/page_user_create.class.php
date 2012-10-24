<?php
/**
 * @title 管理者追加
 */
class page_user_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["User"])){
			try{
				SOY2::cast($this->user,$_POST["User"]);
				$this->user->setPassword($this->user->hashPassword($this->user->getPassword()));
				$this->user->save();
				
				SOY2FancyURIController::redirect("user/detail/" . $this->user->getId() . "?created");
				
			}catch(Exception $e){
				
			}
			
			$this->user->setPassword("");
			$_GET["failed"] = true;
		}
	}
	
	private $user;
	
	function init(){
		$this->user = new Plus_User();
	}
	
	function page_user_create(){
		WebPage::WebPage();
		
		$this->createAdd("create_form","_class.form.UserForm",array(
			"user" => $this->user
		));
	}
}