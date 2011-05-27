<?php
/**
 * @title 管理者詳細
 */
class page_user_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["User"])){
			SOY2::cast($this->user,$_POST["User"]);
			$this->user->save();
			
			//
			$session = SOY2Session::get("base.session.UserLoginSession");
			$session->setName($this->user->getName());
			
			$this->jump("/user/detail/" . $this->id);
		}
		
	}
	
	private $id;
	private $user;
	
	function init(){
		
		//自分だった場合はProfileに移動
		$session = SOY2Session::get("base.session.UserLoginSession");
		if($session->getId() == $this->id){
			$this->jump("/user/profile");
		}
		
		try{
			$this->user = SOY2DAO::find("SOYCMS_User",$this->id);
		}catch(Exception $e){
			$this->jump("/user");
		}
	}

	function page_user_detail($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->addLabel("user_name_text",array(
			"text" => $this->user->getName()
		));
		
		$this->createAdd("detail_form","_class.form.UserForm",array(
			"user" => $this->user
		));
		
	}
}