<?php
//import classes
SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("admin.domain.SOYCMS_User");
SOY2::imports("site.domain.*");
SOY2::imports("site.public.base.*");
SOY2::imports("site.public.base.func.*");
SOY2::imports("site.public.base.class.*");
SOY2::imports("site.public.base.page.*");

//import class
SOY2::import("plugin.PluginManager");
SOY2::imports("ext.*");
SOY2::imports("i18n.base.*"); //new

//init
SOY2PageController::init("SOYCMS_SiteController");

//configure extension
$ext_dir = SOYCMSConfigUtil::get("ext_dir");
if(!$ext_dir)$ext_dir = dirname(SOYCMS_ROOT_DIR) . "/" . basename(SOYCMS_ROOT_DIR) . "_ext/";
if(file_exists($ext_dir.SOYCMS_SITE_ID.".conf.php")){
	SOY2::RootDir($ext_dir);
	require($ext_dir.SOYCMS_SITE_ID.".conf.php");
}