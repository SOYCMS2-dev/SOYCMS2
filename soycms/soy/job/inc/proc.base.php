<?php
$_argv = array();

function ___log($str){
	echo "[LOG][" . date("Y-m-d H:i:s") . "] ";
	echo $str . "\n";
}
function ___error($str){
	echo "[ERROR][" . date("Y-m-d H:i:s") . "] ";
	echo $str . "\n";
}

foreach($argv as $val){
	if(strpos($val, "--config-dir=") !== false){
		$_SERVER["CONFIG_DIR"] = str_replace('"',"",substr($val, strlen("--config-dir=")));
		$_argv[] = $val;
		continue;
	}
	if(strpos($val, "--db-dir=") !== false){
		$_SERVER["CONFIG_DB_DIR"] = str_replace('"',"",substr($val, strlen("--config-dir=")));
		$_argv[] = $val;
		continue;
	}
}