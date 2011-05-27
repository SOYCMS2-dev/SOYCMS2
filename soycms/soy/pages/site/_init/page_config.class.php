<?php
/**
 * @class page_confirm
 * @date 2010-04-08T21:04:57+09:00
 * @author SOY2HTMLFactory
 */ 
class page_config extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$config = $_POST["Config"];
		$site = $session->getSite();
		
		//戻る
		if(isset($_POST["go_back"])){
			$session->setConfig(array());
			$this->jump("?init_site=back");
			exit;
		}
		
		
		$logic = SOY2Logic::createInstance("site.logic.init.InitLogic");
		$res = $logic->testConfig($site,$config);
		
		//次へ
		if($res === true){
			$session->setConfig($config);
			$this->jump("/template?init_site");
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
		
	function page_config(){
		
		WebPage::WebPage();
		
		$this->createAdd("update_form","HTMLForm");
		
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
		SOY2::imports("_class.component.*",SOYCMS_COMMON_DIR . "pages/site/");
		
		$this->addForm("form");
		$config = $this->config;
		
		$this->addSelect("config_encoding",array(
			"name" => "Config[encoding]",
			"options" => array("UTF-8","EUC-JP","Shift-JIS"),
			"selected" => @$config["encoding"]
		));
		$this->createAdd("config_timezone_select","TimeZoneSelectComponent",array(
			"name" => "Config[timezone]",
			"selected" => (isset($config["selected"])) ? @$config["selected"] : date_default_timezone_get()
		));
		$this->addCheckbox("config_use_workflow",array(
			"name" => "Config[workflow]",
			"value" => 1,
			"selected" => (@$config["workflow"] == 1)
		));
		$this->addCheckbox("config_not_use_workflow",array(
			"name" => "Config[workflow]",
			"value" => 0,
			"selected" => (@$config["workflow"] == 0)
		));
		$this->addInput("config_upload",array(
			"name" => "Config[upload]",
			"value" => (!@$config["upload"]) ? "files/" : @$config["upload"]
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