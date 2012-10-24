<?php

class page_logout extends PlusUserWebPageBase{
	
	function doPost(){
		$session = SOY2Session::get("PlusUserSiteLoginSession");
		$session->deleteCookie();
		$session->destroy();
		
		session_regenerate_id();
		
		$config = PlusUserConfig::getConfig();
		$uri = $config->getOption("logout_forward_uri");
		PlusUserApplicationHelper::getController()->jump(soycms_get_page_url(soycms_union_uri($uri)));
		exit;
				
	}
	
	function init(){
		PlusUserApplicationHelper::putTopicPath("plus_user_connector.logout","ログアウト");
	}

	function page_logout() {
		WebPage::WebPage();
	}
	
	function buildPage(){
		$this->addForm("logout_form",array(
			"soy2prefix" => "cms"
		));
	}
	
	
}
?>