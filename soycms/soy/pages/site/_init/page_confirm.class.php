<?php
/**
 * @class page_confirm
 * @date 2010-04-08T21:04:57+09:00
 * @author SOY2HTMLFactory
 */ 
class page_confirm extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$site = $session->getSite();
		$config = $session->getConfig();
		
		//戻る
		if(isset($_POST["go_back"])){
			$this->jump("/?init_site=back");
		}
		
		
		//一個戻る
		if(isset($_POST["go_template"])){
			$this->jump("/template?init_site=back");
			exit;
		}
		
		//実行！！！！！！
		$logic = SOY2Logic::createInstance("site.logic.init.InitLogic");
		
		try{
			if($logic->testAll($site)){
				
				ob_start();
				$res = $logic->initialize($site,$config);
				$log = ob_get_contents();
				ob_end_clean();
				
				if($res){
					$session->destroy();
					
					//show complete
					$this->jump("/complete?init_site=" . $site->getId());
				}
				
				echo $log;
			}
		}catch(Exception $e){
			$site->delete();
			$this->jump("/?init_site&init_error=" . $e->getMessage());
		}
		
		exit;
	}
	
	function init(){
		$session = SOY2Session::get("site.session.SiteCreateSession");
		$this->site = $session->getSite();
		$this->config = $session->getConfig();
	}
	
	var $site;
	var $config;
		
	function page_confirm(){
		
		WebPage::WebPage();
		
		if(!$this->site){
			$this->jump("?init_site");
		}
		
		$this->createAdd("update_form","HTMLForm");
		
		$site = $this->site;
		
		$this->createAdd("site_id","HTMLLabel",array(
			"text" => $site->getSiteId()
		));
		
		$this->createAdd("site_name","HTMLLabel",array(
			"text" => $site->getName()
		));
		
		$this->createAdd("site_dir","HTMLLabel",array(
			"text" => $site->getPath()
		));
		
		$this->createAdd("site_url","HTMLLink",array(
			"link" => $site->getUrl(),
			"text" => $site->getUrl()
		));
		
		$this->addLabel("site_url_text",array(
			"text" => $site->getUrl()
		));
		
		$this->createAdd("site_value","HTMLInput",array(
			"name" => "site_value",
			"value" => base64_encode(soy2_serialize($site))
		));
		
		$config = $site->getConfigObject();
		
		$this->createAdd("db_type","HTMLLabel",array(
			"text" => $config["dbtype"]
		));
		
		$this->createAdd("mysql_dsn","HTMLLabel",array(
			"text" => @$config["dsn"]
		));
		
		$this->createAdd("mysql_user","HTMLLabel",array(
			"text" => @$config["user"],
		));
		
		$this->createAdd("mysql_pass","HTMLLabel",array(
			"text" => @$config["pass"],
		));
		
		$this->createAdd("mysql","HTMLModel",array(
			"visible" => ($config["dbtype"] == "mysql")
		));
		
		$this->addLabel("config_encoding",array(
			"text" => $this->config["encoding"]
		));
		
		$this->addLabel("config_timezone",array(
			"text" => $this->config["timezone"]
		));
		
		$this->addLabel("config_upload",array(
			"text" => $site->getURL() . $this->config["upload"]
		));
		
		
		$this->addLabel("config_template",array(
			"text" => $this->getTemplateInfo($this->config["template"])
		));
	}
	
	function getTemplateInfo($template){
		
		if(!is_numeric($this->config["template"])){
			$obj = SOYCMS_Skeleton::load($this->config["template"]);
			if($obj){
				return $obj->getName();
			}
			
			$template = $this->config["template"] = 0;
		}
		
		
		return	 ($template == 0) ? "標準テンプレート1" :
				(($template == 1) ? "標準テンプレート2" : "ブランクテンプレート");
	}
	
	function setSite($site){
		$this->site = $site;
	}
	
	
	function getLayout(){
		return "frame.php";
	}
}


?>