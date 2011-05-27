<?php

class page_ajax_check_site_login extends SOYCMS_WebPageBase{

    function page_ajax_check_site_login() {
    	$_GET["login"] = 1;
    	
    	$session = SOY2Session::get("base.session.UserLoginSession");
    	if($session->isSuperUser()){
    		echo 1;
    		exit;
    	}
    	
    	$userId = $session->getId();
    	
    	$siteId = $_REQUEST["id"];
    	$site = SOY2DAO::find("SOYCMS_Site",$siteId);
    	
    	SOYCMSConfigUtil::loadConfig("site/" . $site->getSiteId() . ".conf.php");
    	
    	SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::pass(SOYCMS_SITE_DB_PASS);
		
		SOY2::import("site.domain.SOYCMS_Role");
		$roles = SOY2DAO::find("SOYCMS_Role",array("adminId"=> $userId));
		
		echo count($roles);
		
    	exit;
    }
}
?>