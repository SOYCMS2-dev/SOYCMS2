<?php
SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("site.domain.SOYCMS_Role");

/**
 * サイトにログインする
 * セッション周りの問題で、?loginがついていないとログイン出来ない
 */
class page_site_login extends SOYCMS_WebPageBase{

	function page_site_login($args) {
		
		SOY2Session::destroySession("site.session.SiteLoginSession");
		$_GET["login"] = 1;
		
		$id = $args[0];
		$site = SOY2DAO::find("SOYCMS_Site",$id);
		
		$userLoginSession = SOY2Session::get("base.session.UserLoginSession");
		
		$session = SOY2Session::get("site.session.SiteLoginSession");
		$session->login($site,$userLoginSession->getId());
		
		//autologin
		$session = SOY2Session::get("base.session.AutoLoginSession");
		if($session->getId()){
			$session->setSiteId($site->getId());
			$session->save();
		}
		
		//SiteUserLoginSessionと同期
		$userLoginSession = SOY2Session::get("site.session.SiteUserLoginSession");
		$userLoginSession->setSiteId($site->getSiteId());
		$userLoginSession->setSoycmsRoot(SOY2FancyURIController::createRelativeLink("../",true));
		
		//最後にログインしたサイトを記録
		SOYCMS_UserData::put("last_login_site",$id);
		
		SOY2FancyURIController::redirect("../site/index.php");
		exit;
	}
}
?>