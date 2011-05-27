<?php
/**
 * @title Webサイトの設定
 */
class page_config_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		//キャッシュのクリア
		if(isset($_POST["clear_cache"])){
			
			//キャッシュ
			if(defined("SOYCMS_SITE_DIRECTORY")){
				$dir = SOYCMS_SITE_DIRECTORY . ".cache/";
				$files = soy2_scandir($dir);
				
				foreach($files as $file){
					if(is_dir($dir . $file)){
						soy2_delete_dir($dir . $file);
						continue;
					}
					@unlink($dir . $file);
				}
			}
			
			//管理側
			if(file_exists(SOYCMS_ROOT_DIR . "tmp/")){
				$dir = SOYCMS_ROOT_DIR . "tmp/";
				$files = soy2_scandir($dir);
				
				foreach($files as $file){
					if(is_dir($dir . $file)){
						soy2_delete_dir($dir . $file);
						continue;
					}
					@unlink($dir . $file);
				}
			}
			
			$this->jump("/config?clear");
		}
		
		//upload
		if(isset($_FILES["favicon_upload"]) && $_FILES["favicon_upload"]["size"] > 0){
			$ext = pathinfo($_FILES["favicon_upload"]["name"]);
			$ext = $ext["extension"];
			if(!file_exists(SOYCMS_SITE_DIRECTORY . "themes/icons/")){
				soy2_mkdir(SOYCMS_SITE_DIRECTORY . "themes/icons/");
			}
			if(move_uploaded_file($_FILES["favicon_upload"]["tmp_name"], SOYCMS_SITE_DIRECTORY . "themes/icons/favicon.ico")){
				
				if(class_exists("Imagick")){
					try{
						$path = SOYCMS_SITE_DIRECTORY . "themes/icons/favicon.ico";
						$Imagick = new Imagick($path);
						$Imagick->cropThumbnailImage(32,32);
						$Imagick->setFormat('ico');
						$Imagick->writeImage($path);
					}catch(Exception $e){
						
					}
				}
			}
		}
		
		if(isset($_POST["Data"])){
			foreach($_POST["Data"] as $key => $value){
				
				//履歴の個数だけ数値
				if($key == "entry_history_count"){
					$value = min(100,$value * 1);
				}
				if($key == "template_history_count"){
					$value = min(100,$value * 1);
				}
				
				
				SOYCMS_DataSets::put($key,$value);
			}
			
			//sync
			if($_POST["Data"]["site_name"]){
				$name = $_POST["Data"]["site_name"];
				
				try{
					$site = SOY2DAO::find("SOYCMS_Site",array("siteId" => SOYCMS_LOGIN_SITE_ID));
					$site->setName($name);
					$site->save();
				}catch(Exception $e){
					//do nothing
				}
				
				$session = SOY2Session::get("site.session.SiteLoginSession");
				$session->setSiteName($name);
			}
			
			//maintenance中に設定された時
			if(SOYCMS_DataSets::get("mode_maintenance",null)){
				try{
					$page = SOY2DAO::find("SOYCMS_Page",array("uri" => "maintenance.html"));
				}catch(Exception $e){
					//maintenance.htmlが無い場合は自動で生成する
					$pageLogic = SOY2Logic::createInstance("site.logic.page.SOYCMS_BuildMaintenancePageLogic");
					$pageLogic->generate();
				}
			}
			
			//自動保存周り
			if(isset($_POST["Data"]["is_use_autosave"])){
				$value = $_POST["Data"]["is_use_autosave"];
				SOY2DAOFactory::create("SOYCMS_EntryAttributeDAO")->toggleByClassName("autosave",soy2_serialize($value));
			}
				
		}
		
		$this->jump("/config?updated");
		
	}

	function page_config_index(){
		WebPage::WebPage();
		
		$this->addUploadForm("form");
		$this->buildForm();
	}
	
	function buildForm(){
		$this->addInput("site_name",array(
			"name" => "Data[site_name]",
			"value" => SOYCMS_DataSets::get("site_name","")
		));
		
		$this->addLabel("site_directory",array(
			"text" => SOYCMS_SITE_DIRECTORY
		));
		
		$this->addTextArea("site_description",array(
			"name" => "Data[site_description]",
			"value" => SOYCMS_DataSets::get("site_description","")
		));
		
		
		
		$this->addCheckbox("is_use_ssl",array(
			"name" => "Data[is_use_ssl]",
			"value" => 1,
			"isBoolean" => 1,
			"selected" => (SOYCMS_DataSets::get("is_use_ssl",0) == 1),
			"elementId" => "is_use_ssl"
		));
		
		$this->addModel("ssl_config_wrap",array(
			"style" => (SOYCMS_DataSets::get("is_use_ssl",0) == 1) ? "" : "display:none;"
		));
		
		$this->addInput("ssl_url",array(
			"name" => "Data[ssl_url]",
			"value" => SOYCMS_DataSets::get("ssl_url","")
		));
		
		$this->addInput("ssl_directory",array(
			"name" => "Data[ssl_directory]",
			"value" => SOYCMS_DataSets::get("ssl_directory","")
		));
		
		$this->createAdd("timezone","_class.component.TimeZoneSelectComponent",array(
			"name" => "Data[timezone]",
			"value" => SOYCMS_DataSets::get("timezone",date_default_timezone_get())
		));
		
		$this->addInput("language",array(
			"name" => "Data[site_language]",
			"value" => SOYCMS_DataSets::get("site_language","ja")
		));
		
		$this->addInput("author",array(
			"name" => "Data[site_autor]",
			"value" => SOYCMS_DataSets::get("site_autor","")
		));
		
		$this->addInput("copyright",array(
			"name" => "Data[site_copyright]",
			"value" => SOYCMS_DataSets::get("site_copyright","")
		));
		
		$this->addImage("favicon_img",array(
			"src" => soycms_get_page_url("themes/icons/favicon.ico"),
			"visible" => file_exists(SOYCMS_SITE_DIRECTORY . "themes/icons/favicon.ico") 
		));
		
		/* basic */
		$modeBasicAuth = SOYCMS_DataSets::get("mode_basic_auth",0);
		$this->addCheckbox("mode_basic_auth",array(
			"elementId" => "mode_basic_auth",
			"name" => "Data[mode_basic_auth]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $modeBasicAuth
		));
		$this->addModel("mode_basic_auth_option_wrap",array(
			"style" => ($modeBasicAuth) ? "" : "display:none;"
		));
		$this->addInput("mode_basic_auth_id",array(
			"name" => "Data[mode_basic.id]",
			"value" => SOYCMS_DataSets::get("mode_basic.id","")
		));
		$this->addInput("mode_basic_auth_pass",array(
			"name" => "Data[mode_basic.pass]",
			"value" => SOYCMS_DataSets::get("mode_basic.pass","")
		));
		
		
		$this->addCheckbox("mode_maintenance",array(
			"name" => "Data[mode_maintenance]",
			"isBoolean" => true,
			"value" => 1,
			"selected" => SOYCMS_DataSets::get("mode_maintenance",0)
		));
		
		$this->addInput("entry_history_count",array(
			"name" => "Data[entry_history_count]",
			"value" => SOYCMS_DataSets::get("entry_history_count",20)
		));
		
		$this->addInput("template_history_count",array(
			"name" => "Data[template_history_count]",
			"value" => SOYCMS_DataSets::get("template_history_count",20)
		));
		
		$this->addCheckbox("is_use_autosave",array(
			"name" => "Data[is_use_autosave]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => SOYCMS_DataSets::get("is_use_autosave",1),
			"elementId" => "is_use_autosave"
		));
		
		$this->addCheckbox("is_use_quickmenu",array(
			"name" => "Data[is_use_quickmenu]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => SOYCMS_DataSets::get("is_use_quickmenu",1),
			"elementId" => "is_use_quickmenu"
		));
		
		PluginManager::load("soycms.site.dashboard");
		$dashboard = PluginManager::invoke("soycms.site.dashboard");
		$this->addSelect("dashboard_config",array(
			"name" => "Data[dashboard]",
			"options" => $dashboard->getList(),
			"selected" => SOYCMS_DataSets::get("dashboard",-1),
		));
		$this->addModel("has_dashboard_config",array(
			"visible" => count($dashboard->getList()) > 0
		));
		
		$this->addTextArea("update_ping_target",array(
			"name" => "Data[update_ping_target]",
			"value" => SOYCMS_DataSets::get("update_ping_target",implode("\n",array(
				"http://blogsearch.google.com/ping/RPC2",
				"http://api.my.yahoo.co.jp/RPC2",
				"http://rpc.technorati.com/rpc/ping",
				"http://blog.goo.ne.jp/XMLRPC",
				"http://rpc.reader.livedoor.com/ping",
				"http://ping.myblog.jp",
				"http://www.blogpeople.net/servlet/weblogUpdates",
				"http://ping.bloggers.jp/rpc/",
				"http://rpc.weblogs.com/RPC2",
				"http://ping.fc2.com",
				"http://ping.namaan.net/rpc/",
				"http://ping.rss.drecom.jp/",
				"http://ping.ask.jp/xmlrpc.m",
			)))
		));
		
	}
}