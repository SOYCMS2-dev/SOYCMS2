<?php
SOY2::imports("_class.list.*",SOYCMS_COMMON_DIR . "pages/admin/");

/**
 * @class page_template
 * @date 2010-04-08T21:04:57+09:00
 * @author SOY2HTMLFactory
 */ 
class page_template extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["skeleton_upload"]) && isset($_FILES["skeleton"])){
			$tmpname = $_FILES["skeleton"]["tmp_name"];
			
			if(!file_exists(SOYCMS_ROOT_DIR . "content/skeleton/")){
				soy2_mkdir(SOYCMS_ROOT_DIR . "content/skeleton/");
			}
			
			//skeleton uncompress
			$manager = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
			$files = soy2_scandir(SOYCMS_ROOT_DIR . "content/skeleton/");
			$target = sprintf("skeleton-%02d",count($files) + 1);
			
			$manager->uncompress(
				$tmpname,
				SOYCMS_ROOT_DIR . "content/skeleton/" . $target
			);
			
			//copy original file
			move_uploaded_file(
				$tmpname,
				SOYCMS_ROOT_DIR . "content/skeleton/" . $target . "/skeleton.zip"
			);
			
			$this->jump("/template?init_site");
		}
		
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$site = $session->getSite();
		$config = $session->getConfig();
		if(isset($_POST["Config"])){
			$config["template"] = $_POST["Config"]["template"];
		}
		
		//戻る
		if(isset($_POST["go_back"])){
			$session->setConfig(array());
			$this->jump("/config?init_site=back");
			exit;
		}
		
		$logic = SOY2Logic::createInstance("site.logic.init.InitLogic");
		$res = $logic->testConfig($site,$config);
		
		//次へ
		if($res === true){
			$session->setConfig($config);
			$this->jump("/confirm?init_site");
			exit;
		}
	}
	
	var $site;
	private $config;
	
	function init(){
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$this->site = $session->getSite();
		$this->config = $session->getConfig();
		
		if(!$this->site){
			$this->jump("?init_site");
		}
	}
		
	function page_template(){
		
		WebPage::WebPage();
		
		$this->addForm("update_form");
		$this->addForm("form");
		$this->addUploadForm("skeleton_upload_form");
		
		$site = $this->site;
		
		$this->buildForm();
		$this->buildPage();
	}
	
	function buildPage(){
		$this->addLabel("site_url",array(
			"text" => $this->site->getURL()
		));
	}
	
	function buildForm(){
		
		$config = $this->config;
		
		for($i=0;$i<3;$i++){
			$this->addCheckbox("config_template" . $i,array(
				"name" => "Config[template]",
				"value" => $i,
				"selected" => (@$config["template"] == $i)
			));
		}
		
		$this->createAdd("skeleton_list","SkeletonList",array(
			"selected" => @$config["template"],
		));
	}
	
	function setSite($site){
		$this->site = $site;
	}
	
	
	function getLayout(){
		return "frame.php";
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}


?>