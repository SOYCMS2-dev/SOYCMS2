<?php
/**
 * @title ログイン
 */
class page_login extends WebPage{
	
	function doPost(){
		if(isset($_POST["Login"])){
			session_regenerate_id();
			$session = SOY2Session::get("base.session.UserLoginSession");
			if($session->login($_POST["Login"]["userId"],$_POST["Login"]["password"])){
				
				//cookie login
				if(@$_POST["autologin"]){
					$autoLoginSession = SOY2Session::get("base.session.AutoLoginSession");
					$autoLoginSession->setUserId($session->getId());
					$autoLoginSession->publishCookie();
					$autoLoginSession->save();
				}
				
				if(SOYCMS_UserData::get("last_login_site",0,$session->getId())){
					$siteId = SOYCMS_UserData::get("last_login_site",0,$session->getId());
					SOY2FancyURIController::jump("/site/check/" . $siteId);
					exit;
				}
				
				SOY2FancyURIController::jump("/site/select");
			}
		}
		
		DisplayPlugin::visible("errors");
	}

	function page_login(){
		DisplayPlugin::hide("errors");
		
		$session = SOY2Session::get("base.session.UserLoginSession");
		if($session->isLoggedIn()){
			SOY2FancyURIController::jump("/site/");
		}
		
		WebPage::WebPage();
		
		$this->addForm("login_form");
		$this->addInput("user_id",array(
			"name" => "Login[userId]",
			"value" => ""
		));
		
		$this->addInput("password",array(
			"name" => "Login[password]",
			"value" => ""
		));
		
		$this->buildPages();
		
	}
	
	function buildPages(){
		//ロゴ画像
		$config = SOYCMS_CommonConfig::get("config_custom",array(
			"login_icon" => 0,
			"cp_theme" => "gray",
			"allowKeepLogin" => 1
		));
		$iconPath = "content/admin_icon_login.png";
		
		if(isset($_GET["cp_theme"])){
			$config["cp_theme"] = $_GET["cp_theme"];
		}
		
		$this->addImage("login_logo",array(
			"src" => (@$config["login_icon"] == 1 && file_exists(SOYCMS_ROOT_DIR . $iconPath)) ?
				SOYCMS_ROOT_URL . $iconPath
				: SOY2FancyURIController::createRelativeLink("../common/cp_theme/gray/img/logo_login.png")
		));
		
		//Cookieログイン
		$this->addModel("allow_cookie_login",array(
			"visible" => (@$config["allowKeepLogin"] == 1)
		));
		
		//パスワードリマインダー
		$this->addModel("allow_password_reminder",array(
			"visible" => (@$config["allowPasswordReminder"] == 1 || !isset($config["allowPasswordReminder"]))
		));
		
		//テーマセレクト
		$colors = array("aqua"=>"アクア","black"=>"ブラック","blue"=>"ブルー","gray"=>"グレイ","green"=>"グリーン","olive"=>"オリーブ","white"=>"ホワイト");
		$this->addSelect("theme_select",array(
			"name" => "cp_theme",
			"selected" => $config["cp_theme"],
			"options" => $colors,
			"onchange" => 'location.search="cp_theme="+$(this).val();'
		));
		
		$session = SOY2Session::get("base.session.UserLoginSession");
		$session->setTheme($config["cp_theme"]);
		
		
	}
	
	function getLayout(){
		return "login.php";
	}
}