<?php
function soycms_create_link($url,$isAbsolute = false){
	if(strpos($url,"../") !== false){
		return SOY2FancyURIController::createRelativeLink($url,$isAbsolute);
	}
	return SOY2FancyURIController::createLink($url,$isAbsolute);
}
?>