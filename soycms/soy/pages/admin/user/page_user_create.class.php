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
				
				/**
				 * サイトにログインしている場合
				 */
				$session = SOY2Session::get("site.session.SiteLoginSession");
				if($session->getId()){
					$session->applyConfig();
					
					$dao = SOY2DAOFactory::create("SOYCMS_RoleDAO");
					$dao->setRoles($this->user->getId(),array("super","designer","editor","publisher"));
				}
				
				SOY2FancyURIController::redirect("../site/user/detail/" . $this->user->getId() . "?created");
				
			}catch(Exception $e){
				
			}
			
			$this->user->setPassword("");
			$_GET["failed"] = true;
		}
	}
	
	private $user;
	
	function init(){
		$this->user = new SOYCMS_User();
	}
	
	function page_user_create(){
		WebPage::WebPage();
		
		$this->createAdd("create_form","_class.form.UserForm",array(
			"user" => $this->user
		));
	}
}