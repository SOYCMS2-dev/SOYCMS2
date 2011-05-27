<?php
/**
 * @param source
 * @param dest
 */
function soy2_copy($src,$dst,$func = null){
	
	if(!file_exists($dst) && is_dir($src)){
		soy2_mkdir($dst);
	}
	
	$src = soy2_realpath($src);
	$dst = (file_exists($dst)) ? soy2_realpath($dst) : $dst;
	
	if(!file_exists($src)){
		return;
	}
	
	if(is_dir($src)){
		
		$files = soy2_scandir($src);
		
		foreach($files as $file){
			soy2_copy($src . $file, $dst . $file,$func);
		}
		
	}else{
		if(is_dir($dst)){
			$dst = $dst . basename($src);
		}else if(!file_exists(dirname($dst))){
			soy2_mkdir(dirname($dst));
		}
		copy($src,$dst);
		
		if($func){
			call_user_func($func,$dst);
		}
	}
	
}
