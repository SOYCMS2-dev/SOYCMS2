<?php

class AppSession extends SOY2Session{
	
	private $applications = array();
	
	function init(){
		
		//ログイン可能なアプリケーションを全部取得
		$files = soy2_scandir(SOYCMS_COMMON_DIR . "app/");
		
		foreach($files as $file){
			$res = @parse_ini_file(SOYCMS_COMMON_DIR . "app/" . $file . "/application.ini");
			if($res && $res["app_link_title"]){
				//$this->applications[$file] = $res["app_link_title"];
			}
		}
	}
	
	
	/* getter setter */
	
	

	function getApplications() {
		return $this->applications;
	}
	function setApplications($applications) {
		$this->applications = $applications;
	}
}
?>