<?php
/**
 * @title 管理者追加
 */
class page_group_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["Group"])){
			try{
				SOY2::cast($this->group,$_POST["Group"]);
				$this->group->save();
				SOY2FancyURIController::redirect("group/detail/" . $this->group->getId() . "?created");
				
			}catch(Exception $e){
				
			}
			
			$_GET["failed"] = true;
		}
		
	}
	
	private $Group;
	
	function init(){
		$this->group = new Plus_Group();
	}
	
	function page_group_create(){
		WebPage::WebPage();
		
		$this->createAdd("create_form","_class.form.GroupForm",array(
			"Group" => $this->group
		));
	}
}