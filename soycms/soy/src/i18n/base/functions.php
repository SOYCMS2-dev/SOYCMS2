<?php
function init_site_i18n($lang = "ja"){
	//set soy2html language
	SOY2HTMLConfig::Language($lang);
	
	//language file directory
	$lang_dir = SOYCMS_SITE_DIRECTORY . ".i18n/";
	if(!file_exists($lang_dir))return;
	if(!file_exists($lang_dir . "site.json"))return;
	
	//多言語対応
	SOY2String::language($lang);
	
	if(defined("SOYCMS_ADMIN_LOGINED") && SOYCMS_ADMIN_LOGINED){
		//管理画面にログイン中はキャッシュしない
	}else{
		SOY2String::doCache(SOY2HTMLConfig::CacheDir());
	}
	
	SOY2String::load($lang_dir . "site.json");
}
