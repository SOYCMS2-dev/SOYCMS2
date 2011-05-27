<?php

SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("admin.domain.SOYCMS_User");
SOY2::import("admin.domain.SOYCMS_CommonConfig");
SOY2::imports("site.domain.*");
SOY2::imports("site.domain.history.*");
SOY2::imports("site.domain.group.*");
SOY2::imports("site.public.base.func.*");

SOY2HTMLPlugin::addPlugin("quickhelp","QuickHelpPlugin");

//初期化の場合
if(isset($_GET["init_site"])){
	SOY2HTMLConfig::PageDir(SOYCMS_COMMON_DIR . "pages/site/_init/");
	SOY2HTMLConfig::TemplateDir(SOYCMS_COMMON_DIR . "template/site/_init/");
	
	SOY2::import("admin.domain.SOYCMS_Skeleton");
	
//通常
}else{
	SOY2HTMLConfig::PageDir(SOYCMS_COMMON_DIR . "pages/site/");
	SOY2HTMLConfig::TemplateDir(SOYCMS_COMMON_DIR . "template/site/");
	
	$session = SOY2Session::get("site.session.SiteLoginSession");
	if(!$session || !$session->getId()){
		SOY2FancyURIController::redirect("../admin/site?not_login&return_url=" . rawurldecode($_SERVER["REQUEST_URI"]));
	}
	
}