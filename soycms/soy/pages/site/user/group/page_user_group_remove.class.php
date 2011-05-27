<?php
SOY2::import("admin.domain.SOYCMS_User");

class page_user_group_remove extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			$this->group->delete();
			$this->jump("/user/group?deleted");
		}
		
	}

	function init(){
		try{
			$this->group = SOY2DAO::find("SOYCMS_Group",$this->id);
		}catch(Exception $e){
			$this->jump("/user/group");
		}
	}
	
	private $id;
	private $error = false;

	function page_user_group_remove($args) {
		$this->id = $args[0];
		
		WebPage::WebPage();
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		$this->createAdd("form","_class.form.GroupForm",array(
			"group" => $this->group
		));	
		
		$this->addLink("detail_link",array(
			"link" => soycms_create_link("/user/group/detail/" . $this->group->getId())
		));
		
	}
	
}
?>