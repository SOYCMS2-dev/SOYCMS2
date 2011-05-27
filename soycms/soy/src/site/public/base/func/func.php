<?php

/**
 * ページのURLを取得する
 */
function soycms_get_page_url($uri,$suffix = null){
	
	if($uri == "_home"){
		$uri = "";
	}

	if($suffix){
		return soycms_union_uri(soycms_get_site_url(true),$uri,$suffix);
	}

	return soycms_union_uri(soycms_get_site_url(true),$uri);

}

/**
 * ファイルのURLを取得する
 */
function soycms_get_file_url($uri){
	return soycms_union_uri(SOYCMS_SITE_URL,$uri);
}

/**
 * サイトのURLを取得する
 */
function soycms_get_site_url($isAbsolute = false){
	$url = SOYCMS_SITE_ROOT_URL;
	
	if($isAbsolute){
		return $url;
	}else{
		return preg_replace('/h[a-z]+:\/\/[^\/]+/','',$url);
	}
}

/**
 * サイトのURIを取得する
 */
function soycms_get_site_path(){
	$url = parse_url(SOYCMS_SITE_URL);
	return $url["path"];
}

