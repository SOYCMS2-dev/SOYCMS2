<?php
/**
 * @title ルート設定
 */
class page_site_root extends SOYCMS_WebPageBase{
	
	private $id;
	private $site;
	
	function doPost(){
		
		//戻る
		if(isset($_POST["back"])){
			$this->jump("/site/detail/" . $this->id);
		}
		
		//保存
		if(isset($_POST["save"]) && soy2_check_token()){
			
			$root = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
			$index_php = $_POST["root"]["index_php"];
			$htaccess = $_POST["root"]["htaccess"];
			
			file_put_contents($root . "index.php", $index_php);
			file_put_contents($root . ".htaccess", $htaccess);
			
		}
		
		
		//一覧に戻る
		$this->jump("/site");
		
	}
	
	function init(){
		try{
			$this->site = SOY2DAO::find("SOYCMS_Site",$this->id);
		}catch(Exception $e){
			$this->jump("/site");
		}
		
	}

	function page_site_root($args){
		$this->id = $args[0];
		WebPage::WebPage();
		$this->buildPage();
		
		
		$this->addForm("form");
		
		$this->buildForm();
		
	}
	
	function buildPage(){
		
		$this->addLabel("site_name_text",array("text" => $this->site->getName()));
		$this->addLabel("site_id_text",array("text" => $this->site->getSiteId()));
		
		$this->addLink("login_link",array("link" => soycms_create_link("/site/login/" . $this->site->getId())));
		
		//index.php,.htaccessの書き込み権限をチェック
		$root = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
		
		if(file_exists($root . "index.php")){
			$writable = is_writable($root . "index.php") ;
		}else{
			$writable = is_writable($root);	
		}
		
		if(file_exists($root . ".htaccess")){
			$writable = $writable && is_writable($root . ".htaccess");
		}
		
		$this->addModel("error_message",array("visible" => !$writable));
	}
	
	function buildForm(){
		$this->addForm("form");
		
		list($index_php,$htaccess) = $this->getControllers();
		
		$this->addTextArea("index_php",array(
			"name" => "root[index_php]",
			"value" => $index_php
		));
		
		$this->addTextArea("htaccess",array(
			"name" => "root[htaccess]",
			"value" => $htaccess
		));
		
	}
	
	function getControllers(){
		$root = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
		
		$index_php = @file_get_contents($root . "index.php");
		$siteId = SOYCMS_CommonConfig::get("DomainRootSite",null);
		$isDomain = ($siteId == $this->site->getSiteId()); 
		
		
		//不要な箇所を切り取る
		$token_start = "/* @@ SOYCMS Root Script @@ */";
		$token_end = "/* @@ END @@ */";
		
		$index_php = substr($index_php,0,strpos($index_php,$token_start));
		$index_php = substr($index_php,strpos($index_php,$token_end));
		
		if(strlen($index_php)>0){
			$index_php .= "\n";
		}else{
			$token_start = "<?php \n" . $token_start;
		}
		
		//index.phpを作成する
		$index_php .= 	$token_start . "\n" . 
						(($siteId) ?
							'define("SOYCMS_DOMAIN_ROOT", true);' . "\n" .  
							'include("'.$this->site->getPath().'index.php");'
						 : "") . "\n" .
						$token_end . "\n";
		
		
		$htaccess = @file_get_contents($root . ".htaccess");
		$token_start = "## @@ SOYCMS Root Script @@ ##";
		$token_end = "## @@ END @@ ##";
		
		$htaccess = substr($htaccess,0,strpos($htaccess,$token_start));
		$htaccess = substr($htaccess,strpos($htaccess,$token_end));
		
		if(strlen($htaccess)>0)$htaccess .= "\n";
		
		$htaccess .= 	$token_start . "\n" .
						(($siteId) ?
							"RewriteEngine on\n" .
							"RewriteCond %{REQUEST_FILENAME} !-f\n" .
							"RewriteCond %{REQUEST_FILENAME}/index.php !-f\n" .
							"RewriteCond %{REQUEST_FILENAME}/index.html !-f\n" .
							"RewriteCond %{REQUEST_FILENAME}/index.htm !-f\n" .
							"RewriteCond %{REQUEST_URI} !/index.php/\n" .
							'RewriteRule ^(.*)$ index.php?soycms_pathinfo=$1 [QSA,L]' ."\n"
						: "").
						$token_end;
		
		return array($index_php,$htaccess);
	}
}