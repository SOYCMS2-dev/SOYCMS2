<?php
define("PLUSUSER_ROOT_DIR", soy2_realpath(dirname(__FILE__)));


SOY2::RootDir(PLUSUSER_ROOT_DIR . "src/");


SOY2HTMLConfig::PageDir(PLUSUSER_ROOT_DIR . "pages/");
SOY2HTMLConfig::TemplateDir(PLUSUSER_ROOT_DIR . "pages/");

$session = SOY2Session::get("site.session.SiteLoginSession");
if(!$session || !$session->getId()){
	SOY2FancyURIController::redirect("../admin/site");
}

//権限の確認super userじゃない時は権限のチェック
$userLoginSession = SOY2Session::get("base.session.UserLoginSession");
if(!$userLoginSession->isSuperUser() && !$session->hasRole("plus_user_connector")){
	SOY2FancyURIController::redirect("../site/");
}

SOY2::imports("admin.domain.*");
SOY2::imports("site.domain.history.*");
SOY2::imports("site.public.base.func.*");


SOY2::imports("base.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("domain.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("logic.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("extensions.*", PLUSUSER_ROOT_DIR . "src/");

define("SOYCMS_CACHE_FORCE", SOYCMS_DEVELOPING_MODE);


