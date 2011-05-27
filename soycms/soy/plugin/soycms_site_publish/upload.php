<?php
$ROOT_PATH = $argv[1];

include_once(dirname(__FILE__) . "/src/common.inc.php");
include_once($ROOT_PATH . "soy/common.inc.php");
include_once($ROOT_PATH . "soy/conf/site/public.inc.php");

SitePublishPlugin_Publisher::prepare($argv[2]);	//site id

function output_log(){
	global $start,$argv,$config;
	
	$end = microtime(true);
		
	echo "[".date("Y-m-d H:i:s")."]upload finish\n" . 
		"Total Time:" . ($end - $start);
			
	file_put_contents(
		$config["directory"] . ".upload.log",
		ob_get_clean()
	);
}
register_shutdown_function("output_log");

ob_start();
$start = microtime(true);


//load config
$config = SOYCMS_DataSets::get("soycms_site_publish");
		
//open connection
$con = SitePublishPlugin_FTPHelper::connect(
	$config["ftp"]["host"],
	$config["ftp"]["port"],
	$config["ftp"]["id"],
	$config["ftp"]["password"],
	$config["ftp"]["secure"]
);

if(!$con){
	echo "[ERROR]failed to connet ftp server\n";
	exit;
}

//do upload
$files = soy2_scandir($config["directory"]);
foreach($files as $file){
	SitePublishPlugin_FTPHelper::upload(
		$con,
		$config["directory"] . $file,
		$config["ftp"]["directory"]
	);
}



