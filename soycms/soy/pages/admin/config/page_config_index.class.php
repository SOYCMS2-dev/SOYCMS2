<?php
/**
 * @title 設定
 */
class page_config_index extends SOYCMS_WebPageBase{

	function page_config_index(){
		WebPage::WebPage();
		
		$this->createAdd("info","config.page_config_info");
		$this->createAdd("mail","config.page_config_mail");
		$this->createAdd("custom","config.page_config_custom");
		
	}
}