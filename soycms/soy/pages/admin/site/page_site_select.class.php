<?php
SOY2::import("admin.domain.SOYCMS_Site");

/**
 * @title サイト一覧
 */
class page_site_select extends SOYCMS_WebPageBase{

	function page_site_select(){
		WebPage::WebPage();
		
		$siteDAO = SOY2DAOFactory::create("SOYCMS_SiteDAO");
		$sites = $siteDAO->get();
		
		//サイトが一個の時ログインを試す
		if(count($sites) == 1){
			$site = array_shift(array_values($sites));
			$this->tryLogin($site);
		}
		
		$this->createAdd("site_list","_class.list.SiteList",array(
			"list" => $sites,
		));
		
		$this->addModel("no_site",array(
			"visible" => count($sites)<1
		));
		
		$session = SOY2Session::get("base.session.UserLoginSession");
		$this->addModel("is_super_user",array(
			"visible" => $session->isSuperUser()
		));
	}
	
	function tryLogin($site){
		$userLoginSession = SOY2Session::get("base.session.UserLoginSession");
		$session = SOY2Session::get("site.session.SiteLoginSession");
		$res = $session->login($site,$userLoginSession->getId());
		
		if($res){
			//SiteUserLoginSession
			$userLoginSession = SOY2Session::get("site.session.SiteUserLoginSession");
			$userLoginSession->setSiteId($site->getSiteId());
			$userLoginSession->setSoycmsRoot(SOY2FancyURIController::createRelativeLink("../",true));
			
			SOY2FancyURIController::redirect("../site/index.php");
			exit;
		}
	}
	
	function getLayout(){
		return "init.php";
	}
}