<?php
/*
 * このファイルは必ず参照する
 * soy2のinlucde
 * phpの基本設定
 * base周りの読み込み
 */

//timezone config
if(!ini_get("date.timezone"))date_default_timezone_set("Asia/Tokyo");

//include libraries
require(dirname(__FILE__) . "/lib/soy2_build.php");
require(dirname(__FILE__) . "/lib/magic_quote_gpc.php");
require(dirname(__FILE__) . "/lib/json_lib.php");
require(dirname(__FILE__) . "/lib/fix_cgi.php");

//define directory info
define("SOYCMS_ROOT_DIR",	soy2_realpath(dirname(dirname(__FILE__))));	//soycms install dir
define("SOYCMS_COMMON_DIR",	soy2_realpath(dirname(__FILE__)));			//SOYCMS_ROOT_DIR + /soy/
define("SOYCMS_CACHE_DIR",	SOYCMS_ROOT_DIR . "tmp/");					//SOYCMS_ROOT_DIR + /tmp/
define("SOYCMS_VERSION", @file_get_contents(SOYCMS_ROOT_DIR . "version"));

//debug switch
define("SOYCMS_" . "DEVELOPING_MODE",true);
function SOYCMS_IS_DEBUG(){ return false; }

//configure SOY2
SOY2::RootDir(SOYCMS_COMMON_DIR . "src/");
SOY2::imports("base.*",SOYCMS_COMMON_DIR . "conf/");
SOY2::imports("base.func.*");
SOY2::imports("base.class.*");
SOY2::import("plugin.PluginManager");

//configure url
define("SOYCMS_ROOT_URL", soy2_path2url(dirname(dirname(__FILE__))));	//SOYCMS_ROOT_DIR
define("SOYCMS_COMMON_URL", soy2_path2url(dirname(dirname(__FILE__))) . "common/");	//SOYCMS_ROOT_DIR + /common/

//configure document root configure
if(isset($_SERVER["CONFIG_DIR"]) && isset($_SERVER["CONFIG_DB_DIR"])){
	
	SOYCMSConfigUtil::put("config_dir",soy2_realpath($_SERVER["CONFIG_DIR"]));
	SOYCMSConfigUtil::put("db_dir",soy2_realpath($_SERVER["CONFIG_DB_DIR"]));
	
}else{
	
	@include_once(SOYCMS_COMMON_DIR . "conf/user/doc.conf.php");
	
	//include_config
	if(file_exists(SOYCMS_COMMON_DIR . "user.conf.php")){
		soy2_require(SOYCMS_COMMON_DIR . "user.conf.php") or soycms_go_initialize();
	}else{
		soy2_require(SOYCMS_COMMON_DIR . "conf/user/user.conf.php") or soycms_go_initialize();
	}

}

soy2_require(SOYCMSConfigUtil::get("config_dir") . "db.conf.php") or soycms_go_initialize();

if(isset($_GET["soycms_pathinfo"])){
	$_SERVER["PATH_INFO"] = "/" . $_GET["soycms_pathinfo"];
	unset($_GET["soycms_pathinfo"]);
}

//versionでキャッシュを比較する
if(@file_get_contents(SOYCMS_CACHE_DIR . "version") != @file_get_contents(SOYCMS_ROOT_DIR . "version")){
	//キャッシュを全て削除する
	$dir = SOYCMS_CACHE_DIR;
	$files = soy2_scandir($dir);
	
	foreach($files as $file){
		if(is_dir($dir . $file))continue;
		@unlink($dir . $file);
	}
}

if(!file_exists(SOYCMS_CACHE_DIR . "version"))file_put_contents(SOYCMS_CACHE_DIR . "version", @file_get_contents(SOYCMS_ROOT_DIR . "version"));