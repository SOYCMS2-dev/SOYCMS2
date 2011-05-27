<?php
/*
 * fix_cgi.php
 * Created: 2010/01/20
 *
 */
if(preg_match("/cgi/",php_sapi_name())){

	if(!empty($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["PATH_INFO"]) === false){
		$_SERVER["PHP_SELF"] .= $_SERVER["PATH_INFO"];
	}
	
	define("CGI_MODE",true);

}else{
	
	define("CGI_MODE",false);
	
}