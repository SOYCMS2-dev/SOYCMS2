<?php
/**
 * @title ログイン
 */
class page_logout extends SOYCMS_WebPageBase{

	function page_logout(){
		
		//autoLogin
		$autoLoginSession = SOY2Session::get("base.session.AutoLoginSession");
		$autoLoginSession->deleteCookie();
		
		SOY2Session::destroyAll();
		$this->jump("/login");
	}
}