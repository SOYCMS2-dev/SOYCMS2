<?php
SOY2::imports("admin.domain.*");
SOY2::imports("site.public.base.func.*");

SOY2HTMLPlugin::addPlugin("quickhelp","QuickHelpPlugin");


if(soycms_mode_switch("SOYCMS_INIT_MODE")){
	//初期化モード
}else{
	
	SOY2HTMLConfig::PageDir(SOYCMS_COMMON_DIR . "pages/admin/");
	SOY2HTMLConfig::TemplateDir(SOYCMS_COMMON_DIR . "template/admin/");
	
	SOY2DAOConfig::Dsn(SOYCMS_DB_DSN);
	SOY2DAOConfig::user(SOYCMS_DB_USER);
	SOY2DAOConfig::pass(SOYCMS_DB_PASS);
	
	//auto login
	$session = SOY2Session::get("base.session.UserLoginSession");
	
	if(!$session->isLoggedIn()){
		$autoLoginSession = SOY2Session::get("base.session.AutoLoginSession");
		$autoLoginSession->autoLogin();
	}
	
}