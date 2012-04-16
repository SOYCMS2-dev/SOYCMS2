<?php
if(extension_loaded('gettext')){
	//ok
}else{
	function bindtextdomain(){
		
	}
	function textdomain(){
		
	}
	function gettext($str){
		return $str;
	}
	function _($str){
		return $str;
	}
}

function __(){
	$args = func_get_args();
	if(count($args) < 1)return null;
	$args[0] = gettext($args[0]);
	return call_user_func_array("sprintf",$args);
}

function _e(){
	echo call_user_func_array("__",func_get_args());
}