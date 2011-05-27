<?php
function soy2_delete_dir($dir){
	$dir = soy2_realpath($dir);
	if(is_dir($dir)){
		$files = scandir($dir);
		foreach($files as $file){
			if($file == ".")continue;
			if($file == "..")continue;
			$res = soy2_delete_dir($dir . $file);
			if(!$res){
				return false;
			}
		}
		$res = rmdir($dir);
	}else{
		$res = unlink($dir);		
	}
	
	return $res;
}
