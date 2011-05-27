<?php 
SOY2::import("admin.domain.SOYCMS_Site");

/**
 * @class page_index
 * @date 2010-04-08T19:33:24+09:00
 * @author SOY2HTMLFactory
 */ 
class page_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		$site = SOY2::cast("SOYCMS_Site",(object)$_POST["Site"]);
		
		$logic = SOY2Logic::createInstance("site.logic.init.InitLogic");
		$res = $logic->testAll($site);
		
		if($res === true){
			$session = SOY2Session::get("site.session.SiteCreateSession");
			$session->setSite($site);
			$this->jump("/config?init_site");
		}
		
		$this->site = $site;
		switch($res){
			case "db":
				$this->error = "データーベースに接続出来ませんでした";
				break;
			case "path":
				$this->error = "ドキュメントルートにサイトを作成する事はできません。<br />" .
								"ドキュメントルートにサイトを表示したい場合は、" . 
								" サイト一覧→詳細→ドキュメントルートに設定する から行ってください。";
				break;
			case "check":
				$this->error = SOYCMS_ROOT_DIR . "以下にサイトを作成する事はできません。";
				break;
			default:
				$this->error = "入力された内容を確認してください。";
				break;
		}
		
	}
	
	function init(){
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$this->site = (!$this->site) ? $session->getSite() : $this->site;
		
		if(isset($_GET["init_error"])){
			$this->error = "サイトの作成に失敗しました(".$_GET["init_error"].")";
		}
	}
	
	var $site;
	var $error;
	
	function page_index(){
		WebPage::WebPage();
		
		$this->createAdd("create_form","HTMLForm");
		
		$this->addLabel("error_message",array(
			"html" => $this->error,
			"visible" => (strlen($this->error)>0)
		));
		
		$siteId = "";
		$site = ($this->site) ? $this->site : $this->getNewSite($siteId);
		$newSite = $this->getNewSite($site->getSiteId());
		
		$this->createAdd("site_id","HTMLInput",array(
			"name" => "Site[siteId]",
			"value" => $site->getSiteId()
		));
		
		$this->createAdd("site_name","HTMLInput",array(
			"name" => "Site[name]",
			"value" => $site->getName()
		));
		
		$this->createAdd("site_path","HTMLInput",array(
			"name" => "Site[path]",
			"value" => $site->getPath(),
			"style" => ($site->getPath() != $newSite->getPath()) ? "" : "display:none"
		));
		
		$this->createAdd("site_path_text","HTMLLabel",array(
			"attr:prefix" => soy2_realpath($_SERVER["DOCUMENT_ROOT"]),
			"text" => ($site->getSiteId()) ? $site->getPath() : "-",
		));
		
		$this->createAdd("site_path_text_div","HTMLModel",array(
			"visible" => ($site->getPath() == $newSite->getPath())
		));
		
		$this->createAdd("site_url","HTMLInput",array(
			"name" => "Site[url]",
			"value" => $site->getUrl(),
			"style" => ($site->getUrl() != $newSite->getUrl()) ? "" : "display:none"
		));
		
		$this->createAdd("site_url_text","HTMLLabel",array(
			"attr:prefix" => SOY2PageController::createRelativeLink("/",true),
			"text" => ($site->getSiteId()) ? $site->getUrl() : "-",
		));
		
		$this->createAdd("site_url_text_div","HTMLModel",array(
			"visible" => ($site->getUrl() == $newSite->getUrl())
		));
		
		$config = $site->getConfigObject();
		
		$this->createAdd("db_type_sqlite","HTMLCheckbox",array(
			"elementId" => "db_type_sqlite",
			"name" => "Site[config][dbtype]",
			"value" => "sqlite",
			"selected" => ($config["dbtype"] == "sqlite")
		));
		
		$this->createAdd("db_type_mysql","HTMLCheckbox",array(
			"elementId" => "db_type_mysql",
			"name" => "Site[config][dbtype]",
			"value" => "mysql",
			"selected" => ($config["dbtype"] == "mysql")
		));
		
		$this->createAdd("mysql_dsn","HTMLInput",array(
			"attr:id" => "mysql_dsn",
			"name" => "Site[config][mysql_dsn]",
			"value" => (strlen(@$config["mysql_dsn"])>0) ? $config["mysql_dsn"] : "" 
		));
		
		$this->createAdd("mysql_database","HTMLInput",array(
			"attr:id" => "mysql_database",
			"name" => "Site[config][database]",
			"value" => (strlen(@$config["database"])>0 && !@$config["mysql_auto_create_database"]) ? $config["database"] : "" 
		));
		
		$this->addCheckbox("mysql_specific_database",array(
			"elementId" => "mysql_specific_database",
			"name" => "Site[config][mysql_auto_create_database]",
			"value" => 0,
			"selected" => (!isset($config["mysql_auto_create_database"])) ? true : @!$config["mysql_auto_create_database"]
		));
		
		$this->addCheckbox("mysql_auto_create_database",array(
			"elementId" => "mysql_auto_create_database",
			"name" => "Site[config][mysql_auto_create_database]",
			"value" => 1,
			"selected" => (!isset($config["mysql_auto_create_database"])) ? false : @$config["mysql_auto_create_database"]
		));
		
		$this->createAdd("mysql_user","HTMLInput",array(
			"name" => "Site[config][user]",
			"value" => (strlen(@$config["user"])>0) ? $config["user"] : "" 
		));
		
		$this->createAdd("mysql_password","HTMLInput",array(
			"name" => "Site[config][pass]",
			"value" => @$config["pass"] 
		));
	}
	
	function setSite($site){
		$this->site = $site;
	}
	
	function getNewSite($siteId){
		$site = new SOYCMS_Site();
		$site->setSiteId($siteId);
		if(strlen($siteId)>0)$site->setPath(soy2_realpath($_SERVER["DOCUMENT_ROOT"]) . $siteId . "/");
		if(strlen($siteId)>0)$site->setUrl(SOY2PageController::createRelativeLink("/" . $siteId . "/",true));
		return $site;
	}
	
	function getLayout(){
		return "frame.php";
	}
}


?>