<?php
/**
 * copy from http://www.php.net/manual/ja/function.parse-ini-string.php
 * 20110228 fix for safe mode resitriction
 * 
 */
if(!function_exists('parse_ini_string')){
function parse_ini_string($ini, $process_sections = false, $scanner_mode = null){
	# Generate a temporary file.
	$dir = SOY2HTMLConfig::CacheDir();
	do{
		$tempname = $dir . mt_rand() . ".ini";
		if(file_exists($tempname))$tempname = false;
	}while(!$tempname);
	
	$fp = fopen($tempname, 'w');
	fwrite($fp, $ini);
	$ini = parse_ini_file($tempname, !empty($process_sections));
	fclose($fp);
	@unlink($tempname);
	return $ini;
}

}
?>