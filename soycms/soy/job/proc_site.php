<?php
require(dirname(__FILE__) . "/inc/proc.base.php");
require(dirname(__FILE__) . "/inc/proc.conf.php");
$siteId = null;
$daily = false;

foreach($argv as $val){
	if(strpos($val, "--site=") !== false){
		$siteId = str_replace('"',"",substr($val, strlen("--site=")));
		continue;
	}
	if(strpos($val, "--daily") !== false){
		$daily = true;
		continue;
	}
}

try{
	$site = SOY2DAO::find("SOYCMS_Site",array("siteId" => $siteId));
	if(!file_exists($site->getPath()))throw new Exception("site path");
}catch(Exception $e){
	___error("invalid site");
	exit;
}

SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("admin.domain.SOYCMS_User");
SOY2::import("admin.domain.SOYCMS_CommonConfig");
SOY2::imports("site.domain.*");
SOY2::imports("site.domain.history.*");
SOY2::imports("site.domain.group.*");
SOY2::imports("site.public.base.func.*");


___log("load config");
SOYCMSConfigUtil::loadConfig("site/" . $siteId . ".conf.php");
SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);

if(!defined("SOYCMS_SITE_ID")){
	define("SOYCMS_SITE_ID",	$siteId);
}

if($daily){
	___log("[daily]site process start:" . $siteId);
	PluginManager::load("soycms.site.cron");
	PluginManager::invoke("soycms.site.cron",array(
		"mode" => "daily"
	));
}else{
	___log("site process start:" . $siteId);
	PluginManager::load("soycms.site.cron");
	PluginManager::invoke("soycms.site.cron");
}


___log("site process end");