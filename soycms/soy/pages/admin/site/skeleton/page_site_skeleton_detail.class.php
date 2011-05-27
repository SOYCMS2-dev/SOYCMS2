<?php

/**
 * @title サイト一覧
 */
class page_site_skeleton_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if($_POST["stop"]){
			
		}
		
		if($_POST["start"]){
			
		}
		
		if($_POST["create"]){
			
		}
		
		
	}
	
	private $id;
	private $skeleton;
	
	function init(){
		$this->skeleton = SOYCMS_Skeleton::load($this->id);
		
		if(!$this->skeleton){
			$this->jump("site/skeleton");
		}
	}
	
	function page_site_skeleton_detail($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPages();
		
	}
	
	function buildForm(){
		$this->addForm("form");	
	}
	
	function buildPages(){
		$obj = $this->skeleton;
		$this->addLabel("skeleton_name",array("text" => $obj->getName()));
		$this->addLabel("skeleton_description",array("text" => $obj->getDescription()));
		
		$array = $obj->getInformation();
		
		$this->addLabel("contents_count",array("text" => (int)@$array["contents_count"]));
		$this->addLabel("directory_count",array("text" => (int)@$array["directory_count"]));
		$this->addLabel("template_count",array("text" => (int)@$array["template_count"]));
		$this->addLabel("library_count",array("text" => (int)@$array["library_count"]));
		$this->addLabel("snippet_count",array("text" => (int)@$array["snippet_count"]));
		$this->addLabel("navigation_count",array("text" => (int)@$array["navigation_count"]));
		
		
		$path = "content/skeleton/" . $obj->getId() . "/thumbnail.jpg";
		
    	$this->addImage("skeleton_thumbnail",array("src" => 
    		(file_exists(SOYCMS_ROOT_DIR . $path)) ? 
    			SOYCMS_ROOT_URL . $path : SOYCMS_ROOT_URL . "common/img/nothumb.gif",
    		"attr:alt" => "skeleton-" . $obj	->getId() 
    	));
    	
		
		//skeleton link
		$this->addLink("download_link",array(
			"link" => SOYCMS_ROOT_URL . "content/skeleton/" . $this->id . "/skeleton.zip"
		));
	}
}