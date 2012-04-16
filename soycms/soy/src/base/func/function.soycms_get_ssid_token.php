<?php
function soycms_get_ssid_token(){
	if(!isset($_SESSION["SOYCMS_SSID_TOKEN"]) || time() > (int)@$_SESSION["SOYCMS_SSID_LIMIT"]){
		$_SESSION["SOYCMS_SSID_TOKEN"] = md5(time());
		$_SESSION["SOYCMS_SSID_LIMIT"] = time() + 60 * 60;
	}
	return "SOYCMS_SSID=" . session_id() . "&SOYCMS_SSID_TOKEN=" . $_SESSION["SOYCMS_SSID_TOKEN"];
}