<?php
require(dirname(__FILE__) . "/inc/proc.base.php");
require(dirname(__FILE__) . "/inc/proc.conf.php");

ob_start();

___log("process start");
___log("prepare");

___log("start");

$sites = SOY2DAO::find("SOYCMS_Site");
foreach($sites as $site){
	___log("start site:\n\t" 
		. $site->getSiteId() . "\n\t"
		. $site->getPath()
	);
	system("php " . soy2_realpath(dirname(__FILE__) . "/proc_site.php") . " " . implode(" ", $_argv) . " --site=" . $site->getSiteId());
}

___log("finish");


$log = ob_get_clean();
file_put_contents(SOYCMSConfigUtil::get("config_dir") . "cron.log", $log);
echo $log;