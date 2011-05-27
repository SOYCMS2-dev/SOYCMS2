<?php
/**
 * @title サイトトップ
 */
class page_index extends SOYCMS_WebPageBase{
	
	function init(){
		
		//ダイナミック編集に移動する
		if(isset($_GET["dynamic"])){
			SOY2PageController::redirect(SOYCMS_SITE_URL . "?dynamic&SOYCMS_SSID=" . session_id());
			exit;
		}
		
	}
	
	private $dashboard = null;

	function page_index(){
		WebPage::WebPage();
		
		if(isset($_GET["init"]) && SOYCMS_IS_DEBUG()){
			$site = SOY2DAO::find("SOYCMS_Site",array("siteId" => SOYCMS_LOGIN_SITE_ID));
			
			$logic = SOY2Logic::createInstance("site.logic.init.InitLogic");
			
			try{
				$logic->initDataBase($site);
			}catch(Exception $e){
				
			}
			
			$logic->initConfig($site,array(
				"encoding" => "UTF-8",
				"timezone" => "Asia/Tokyo",
			));
			
			SOY2::import("admin.domain.SOYCMS_Skeleton");
			$logic->initDefaultTemplate($site,array("template" => "hoge"));
			
			SOY2FancyURIController::jump("");
			exit;
		}
		
		$intro = SOYCMS_DataSets::get("finish_intro",0);
		if(!$intro){
			$this->jump("/intro");
		}
		
		
		$this->createAdd("quickmenu","menu.page_menu_index",array(
			"visible" => SOYCMS_DataSets::get("is_use_quickmenu",1)
		));
		
		//入れ替え可能とする
		$dashboard = SOYCMS_DataSets::get("dashboard",-1);
		$this->isDefaultDashboard = ($dashboard == -1 || empty($dashboard));
		if(!$this->isDefaultDashboard){
			PluginManager::load("soycms.site.dashboard",$dashboard);
			$delegate = PluginManager::invoke("soycms.site.dashboard",array(
				"mode" => "page",
				"moduleId" => $dashboard
			));
			$dashboard = $delegate->getPage();
			if(strlen(@$dashboard["page"])>0){
				try{
					ob_start();
					eval("?>" . $dashboard["page"]);
					$page = ob_get_clean();
					$dashboard["page"] = $page;
				}catch(Exception $e){
					
				}
			}
		}
		
		$this->addModel("dashboard",array(
			"visible" => !$this->isDefaultDashboard
		));
		
		$this->addLabel("ext_title",array(
			"html" => (is_array($dashboard)) ? @$dashboard["title"] : ""
		));
		$this->addLabel("ext_page",array(
			"html" => (is_array($dashboard)) ? @$dashboard["page"] : ""
		));
		$this->addModel("ext_config_exists",array(
			"visible" => !empty($dashboard["config"])
		));
		$this->addLink("ext_config",array(
			"link" => (is_array($dashboard)) ? @$dashboard["config"] : ""
		));
		
		//サイトマップ
		$this->createAdd("sitemap","page.page_page_list",array(
			"arguments" => array("sitemap",$this->getPageParam()),
			"visible" => $this->isDefaultDashboard
		));
	}
	
	function getSubMenu(){
		
		if($this->isDefaultDashboard){
			$menu = SOY2HTMLFactory::createInstance("page_index_submenu");
			$menu->display();
		}
		
		if($this->dashboard){
			echo $this->dashboard["submenu"];
		}
		
	}
	
}