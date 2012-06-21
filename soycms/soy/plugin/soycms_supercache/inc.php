<?php

function soycms_supercache_clear_uri($uri = null){
	$dirname = str_replace("/","_","/" . $uri);
	$dir = SOYCMS_SITE_DIRECTORY . ".cache/supercache/";
	$dirs = soy2_scandir($dir);
	
	foreach($dirs as $file){
		if(is_null($uri)){
			soy2_delete_dir($dir . $file);
		}else if(strpos($file, $dirname) === 0){
			soy2_delete_dir($dir . $file);
		}
	}
}

function soycms_supercache_clear(){
	soycms_supercache_clear_uri(null);
	
}

function soycms_supercache_get_directory($uri,$suffix){
	$uri = soycms_union_uri($uri,$suffix);
	$dirname = str_replace("/","_","/" . $uri);
	$dir = SOYCMS_SITE_DIRECTORY . ".cache/supercache/";
	return $dir . $dirname;
}