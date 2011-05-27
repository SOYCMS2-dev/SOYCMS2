<?php
/**
 * @title サイト詳細
 */
class page_site_remove extends SOYCMS_WebPageBase{
	
	private $id;
	private $site;
	
	function doPost(){
		
		//保存
		if(isset($_POST["remove"])){//} && soy2_check_token()){
			if(@$_POST["with_file"] == 1){
				$this->removeDir($this->site->getPath());
			}
			
			//rename db
			$db = SOYCMSConfigUtil::get("db_dir") . "site_" . $this->site->getSiteId() . ".db";
			if(file_exists($db)){
				rename($db, $db . "." . date("YmdHis"));
			}
			
			$this->site->delete();
			
			$this->jump("/site/?deleted");
		}
		
		$this->jump("/site/detail/" . $this->site->getId());
		
		
	}
	
	function init(){
		try{
			$this->site = SOY2DAO::find("SOYCMS_Site",$this->id);
		}catch(Exception $e){
			$this->jump("/site");
		}
		
	}

	function page_site_remove($args){
		$this->id = $args[0];
		WebPage::WebPage();
		$this->buildPage();
		
		
		$this->addForm("form");
		
		$siteId = SOYCMS_CommonConfig::get("DomainRootSite",null);
		$isDomainRoot = ($siteId == $this->site->getSiteId());
		
		$this->addModel("do_domain_root",array(
			"attr:name" => "do_root",
			"visible" => !$isDomainRoot
		));
		
		$this->addModel("undo_domain_root",array(
			"attr:name" => "undo_root",
			"visible" => $isDomainRoot
		));
		
		$this->buildForm();
		
	}
	
	function buildForm(){
		
		$this->addInput("site_url",array(
			"name" => "Site[url]",
			"value" => $this->site->getUrl()
		));
		
		$this->addInput("site_path",array(
			"name" => "Site[path]",
			"value" => $this->site->getPath()
		));
		
	}
	
	function buildPage(){
		
		$this->addLabel("site_name_text",array("text" => $this->site->getName()));
		$this->addLabel("site_id_text",array("text" => $this->site->getSiteId()));
		
		$this->addLink("login_link",array("link" => soycms_create_link("/site/login/" . $this->site->getId()) . "?login"));
		$this->addLink("remove_link",array("link" => soycms_create_link("/site/remove/" . $this->site->getId())));
		
		
	}
	
	function removeDir($dir){
		$dir = realpath($dir);
		if(!$dir)return;
		
		$files = scandir($dir);
		
		foreach($files as $file){
			if($file == ".")continue;
			if($file == "..")continue;
			
			
			if(is_dir($dir . "/" . $file)){
				$this->removeDir($dir . "/" . $file);
			}else{
				@unlink($dir . "/" . $file);
			}
		}
		
		@rmdir($dir);
	}
}