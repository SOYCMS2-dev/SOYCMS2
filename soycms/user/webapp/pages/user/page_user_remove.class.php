<?php
/**
 * @title 管理者詳細
 */
class page_user_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["next"])){
		
			$session = SOY2Session::get("base.session.UserLoginSession");
			
			if($this->user->getId() == $session->getId()){
				$session->destroy();
			}
			
			$this->user->delete();
		}
		
		$this->jump("/user/?deleted");
		
	}
	
	private $id;
	private $user;
	
	function init(){
		try{
			$this->user = SOY2DAO::find("Plus_User",$this->id);
		}catch(Exception $e){
			$this->jump("/user");
		}
	}

	function page_user_remove($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		
		$this->addLabel("user_id_text",array(
			"text" => $this->user->getUserId()
		));
		
		$this->addLabel("user_name_text",array(
			"text" => $this->user->getName()
		));
		
		$this->addForm("remove_form");
		
	}
}