<?php
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");
define("SOY2HTML_AUTO_GENERATE",true);

//session
$session = SOY2Session::get("site.session.SiteLoginSession");
if($session && $session->getSiteId()){
	SOYCMSConfigUtil::loadConfig("site/" . $session->getSiteId() . ".conf.php");
}else{
	SOY2FancyURIController::redirect("../admin");
}

require(dirname(__FILE__) . "/src/FileManager.class.php");