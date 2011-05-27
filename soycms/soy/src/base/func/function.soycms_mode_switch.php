<?php
function soycms_mode_switch($key,$value = null){
	if(!defined($key)){
		if(!is_null($value)){
			define($key,$value);
		}else{
			return false;
		}
	}
	
	return constant($key);
}
