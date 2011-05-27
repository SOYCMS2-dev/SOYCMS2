<?php
/**
 * @title サイト詳細
 */
class page_site_detail extends SOYCMS_WebPageBase{
	
	private $id;
	private $site;
	
	function doPost(){
		
		//ドメインルート設定
		if(isset($_POST["do_root"])){
			SOYCMS_CommonConfig::put("DomainRootSite",$this->site->getSiteId());
			$this->jump("/site/root/" . $this->site->getId());
		}
		
		if(isset($_POST["undo_root"])){
			SOYCMS_CommonConfig::put("DomainRootSite",null);
			$this->jump("/site/root/" . $this->site->getId());
		}
		
		//保存
		if(isset($_POST["save"]) && true || soy2_check_token()){
			SOY2::cast($this->site,$_POST["Site"]);
			$this->site->save();
			$this->updateHtaccess($this->site);
			
			
			$this->jump("/site/detail/" . $this->site->getId() . "?updated");
		}
		
		
		exit;
		
	}
	
	function init(){
		try{
			$this->site = SOY2DAO::find("SOYCMS_Site",$this->id);
		}catch(Exception $e){
			$this->jump("/site");
		}
		
	}

	function page_site_detail($args){
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
	
	/**
	 * RewriteBaseをサイトのURLに応じて書き換える
	 */
	function updateHtaccess($site){
		$path = $site->getPath();
		
		$url = $site->getUrl();
		$siteUrl = parse_url($url);
		$sitePath = $siteUrl["path"];
		if($sitePath[strlen($sitePath)-1] != "/")$sitePath .= "/";
		
		$file = file($path . "/.htaccess");
		$_file = array();
		foreach($file as $line){
			if(preg_match('/^RewriteBase\s+(.*)$/',trim($line),$tmp)){
				if($tmp[1] != $sitePath){
					$line = "RewriteBase " . $sitePath . "\n";
				}
			}
			$_file[] = $line;
		}
		
		file_put_contents($path . "/.htaccess", implode("",$_file));
	}
}