<?php
/**
 * 管理画面のカスタマイズ
 */
class page_config_custom extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["config"])){
			$this->saveConfigure($_POST["config"]);
		}
		if(isset($_FILES["login_icon_custom"])){
			$img = SOYCMS_ROOT_DIR . $this->getCustomIconPath();
			$res = move_uploaded_file($_FILES["login_icon_custom"]["tmp_name"],$img);
		}
		
		$this->jump("/config#tab3");
	}

	function page_config_custom() {
		WebPage::WebPage();
		
		$this->buildForm();
		
	}
	
	function buildForm(){
		$config = $this->getConfigure();
		
		$this->addUploadForm("config_form");
		
		//header icon
		$this->addCheckbox("login_icon_default",array(
			"elementId" => "login_icon_default",
			"name" => "config[login_icon]",
			"value" => "0",
			"selected" => (@$config["login_icon"] == 0)
		));
		$this->addCheckbox("login_icon_custom",array(
			"elementId" => "login_icon_custom",
			"name" => "config[login_icon]",
			"value" => "1",
			"selected" => (@$config["login_icon"] == 1)
		));
		
		$img = $this->getCustomIconPath();
		$this->addImage("login_icon_custom_img",array(
			"visible" => file_exists(SOYCMS_ROOT_DIR . $img),
			"src" => SOYCMS_ROOT_URL . $img
		));
		
		//色選択
		$colors = array("aqua"=>"アクア","black"=>"ブラック","blue"=>"ブルー","gray"=>"グレイ","green"=>"グリーン","olive"=>"オリーブ","white"=>"ホワイト");
		foreach($colors as $color => $label){
			$this->addCheckbox("color_select_".$color,array(
				"name" => "config[cp_theme]",
				"value" => $color,
				"label" => $label,
				"selected" => ($color == $config["cp_theme"])
			));
		}
		
		//Cookieログイン
		$this->addCheckbox("allow_keep_login",array(
			"elementId" => "allow_keep_login",
			"name" => "config[allowKeepLogin]",
			"value" => 1,
			"isBoolean" => 1,
			"selected" => (@$config["allowKeepLogin"] == 1)
		));
		
		//パスワードリマインダー
		$this->addCheckbox("allow_password_reminder",array(
			"elementId" => "allow_password_reminder",
			"name" => "config[allowPasswordReminder]",
			"value" => 1,
			"isBoolean" => 1,
			"selected" => (@$config["allowPasswordReminder"] == 1 || !isset($config["allowPasswordReminder"]))
		));
		
	}
	
	function getConfigure(){
		
		return SOYCMS_CommonConfig::get("config_custom",array(
			"login_icon" => 0,
			"cp_theme" => "gray",
			"allowKeepLogin" => 1
		));
	}
	
	function saveConfigure($config){
		SOYCMS_CommonConfig::put("config_custom",$config);
	}
	
	/**
	 * カスタムアイコンのパスを取得
	 */
	function getCustomIconPath($type = "login"){
		$img = "content/admin_icon_{$type}.png";
		return $img;
	}
	
	function getLayout(){
		return "blank.php";
	}
}
?>