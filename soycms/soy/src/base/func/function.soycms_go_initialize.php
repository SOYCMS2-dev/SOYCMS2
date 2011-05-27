<?php
function soycms_go_initialize(){
	//元のファイル（あれば）をリネーム
	$conf = SOYCMS_COMMON_DIR . "conf/user.conf.php";
	if(file_exists($conf))rename($conf,$conf . "_" . date("Ymd"));
	
	include(SOYCMS_COMMON_DIR . "conf/admin/init.inc.php");
}