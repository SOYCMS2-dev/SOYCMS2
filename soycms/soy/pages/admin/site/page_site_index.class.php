<?php
SOY2::import("admin.domain.SOYCMS_Site");

/**
 * @title サイト一覧
 */
class page_site_index extends SOYCMS_WebPageBase{
	
	function init(){
		if(isset($_GET["not_login"])){
			$session = SOY2Session::get("site.session.SiteLoginSession");
			if($session && $session->getId()){
				if(isset($_GET["return_url"])){
					$return_url = $_GET["return_url"];
					SOY2FancyURIController::redirect($return_url);
				}else{
					SOY2FancyURIController::redirect("../site/");
				}
			}
		}
	}

	function page_site_index(){
		WebPage::WebPage();
		$siteDAO = SOY2DAOFactory::create("SOYCMS_SiteDAO");
		$sites = $siteDAO->get();
		
		$this->createAdd("site_list","_class.list.SiteList",array(
			"list" => $sites
		));
		
		$session = SOY2Session::get("base.session.UserLoginSession");
		$this->addModel("is_super_user",array(
			"visible" => $session->isSuperUser()
		));
		
	}
}