<?php

class page_config_version extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["check_version"])){
			$link = soycms_create_link("/config/version");
			echo "<p class=\"notice\">新しいバージョン:2.0.5があります。<a href=\"$link\">バージョンアップ</a></p>";
			exit;
		}
		
	}

	function page_config_version() {
		WebPage::WebPage();
		
		
		exit;
	}
}
?>