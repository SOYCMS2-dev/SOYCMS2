<?php
$ROOT_PATH = $argv[1];

include_once(dirname(__FILE__) . "/src/common.inc.php");
include_once($ROOT_PATH . "soy/common.inc.php");
include_once($ROOT_PATH . "soy/conf/site/public.inc.php");

$_SERVER["REQUEST_METHOD"] = "get";

function output_log(){
	global $start,$argv;
	
	$end = microtime(true);

	echo "[".date("Y-m-d H:i:s")."]export finish\n" . 
		"Total Export Time:" . ($end - $start);
			
	file_put_contents(
		$argv[5] . ".export.log",
		ob_get_clean()
	);
}
register_shutdown_function("output_log");

ob_start();
$start = microtime(true);

SitePublishPlugin_Publisher::publish(
	$argv[2], //siteId
	$argv[3], //sitePath
	$argv[4], //siteUrl
	$argv[5], //targetpath
	$argv[6] //targeturl
);


