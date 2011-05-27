<?php
/**
 * グループの追加
 */
class page_user_group_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Group"])){
			SOY2::cast($this->group,$_POST["Group"]);
			if($this->group->check()){
				$this->group->save();
				
				$this->jump("/user/group/detail/" . $this->group->getId() . "?created");
			}
		}
		
		$this->error = true;
	}
	
	function init(){
		$this->group = new SOYCMS_Group();
	}
	
	private $error = false;

	function page_user_group_create() {
		WebPage::WebPage();
		
		$this->addModel("is_error",array(
			"visible" => $this->error
		));
		
		$this->buildPages();
	}
	
	function buildPages(){
		
		$this->createAdd("form","_class.form.GroupForm",array(
			"group" => $this->group
		));		
		
	}
	
}
?>