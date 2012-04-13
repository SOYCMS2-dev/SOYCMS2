<?php
/**
 * @title 管理画面のカスタマイズ
 */
class page_config_custom extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["config"])){
			SOYCMS_DataSets::put("config_custom",$_POST["config"]);
			$config = $this->getConfig();
			
			$session = SOY2Session::get("site.session.SiteLoginSession");
			$session->setTheme($config["cp_theme"]);
			$session->setConfig($config);
		}
		
		if(isset($_FILES["header_icon_custom"]) && $_FILES["header_icon_custom"]["size"] > 0){
			$img = SOYCMS_ROOT_DIR . $this->getCustomIconPath();
			
			//変換
			$type = pathinfo($_FILES["header_icon_custom"]["name"]);
			$type = $type["extension"];
			soy2_convert_image_type($_FILES["header_icon_custom"]["tmp_name"],$_FILES["header_icon_custom"]["tmp_name"],$type,"png");
			$res = move_uploaded_file($_FILES["header_icon_custom"]["tmp_name"],$img);
			
			$res = move_uploaded_file($_FILES["header_icon_custom"]["tmp_name"],$img);
		}
		if(isset($_FILES["popup_icon_custom"]) && $_FILES["popup_icon_custom"]["size"] > 0){
			$img = SOYCMS_ROOT_DIR . $this->getCustomIconPath("popup");
			
			//変換
			$type = pathinfo($_FILES["popup_icon_custom"]["name"]);
			$type = $type["extension"];
			soy2_convert_image_type($_FILES["popup_icon_custom"]["tmp_name"],$_FILES["popup_icon_custom"]["tmp_name"],$type,"png");
			$res = move_uploaded_file($_FILES["popup_icon_custom"]["tmp_name"],$img);
			
			soy2_resizeimage($img,$img,50,50);			
		}
		if(isset($_FILES["thumbnail_img"]) && $_FILES["thumbnail_img"]["size"] > 0){
			$img = SOYCMS_ROOT_DIR . "content/thumb_" . SOYCMS_LOGIN_SITE_ID . ".png";
			$res = move_uploaded_file($_FILES["thumbnail_img"]["tmp_name"],$img);
		}
		if(isset($_FILES["favicon"]) && $_FILES["favicon"]["size"] > 0){
			$img = SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . ".ico";
			$res = move_uploaded_file($_FILES["favicon"]["tmp_name"],$img);
		}
		if(isset($_POST["custom_css"])){
			$css = SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . ".css";
			file_put_contents($css,$_POST["custom_css"]);
		}
		if(isset($_POST["parts_footer"])){
			SOYCMS_DataSets::put("parts.footer",$_POST["parts_footer"]);
		}
		
		$this->jump("/config/custom?updated");
	}

	function page_config_custom(){
		WebPage::WebPage();
		
		$this->buildForm();
	}
	
	function buildForm(){
		$config = $this->getConfig();
		
		$this->addForm("config_form",array("enctype"=>"multipart/form-data"));
		
		//header icon
		$this->addCheckbox("header_icon_default",array(
			"elementId" => "header_icon_default",
			"name" => "config[header_icon]",
			"value" => "0",
			"selected" => (@$config["header_icon"] == 0)
		));
		$this->addCheckbox("header_icon_custom",array(
			"elementId" => "header_icon_custom",
			"name" => "config[header_icon]",
			"value" => "1",
			"selected" => (@$config["header_icon"] == 1)
		));
		
		$img = $this->getCustomIconPath();
		$this->addImage("header_icon_custom_img",array(
			"visible" => file_exists(SOYCMS_ROOT_DIR . $img),
			"src" => SOYCMS_ROOT_URL . $img
		));
		
		//popup icon
		$this->addCheckbox("popup_icon_default",array(
			"elementId" => "popup_icon_default",
			"name" => "config[popup_icon]",
			"value" => "0",
			"selected" => (@$config["popup_icon"] == 0)
		));
		$this->addCheckbox("popup_icon_custom",array(
			"elementId" => "popup_icon_custom",
			"name" => "config[popup_icon]",
			"value" => "1",
			"selected" => (@$config["popup_icon"] == 1)
		));
		
		$img = $this->getCustomIconPath("popup");
		$this->addImage("popup_icon_custom_img",array(
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
		
		//カスタムCSS
		$css = SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . ".css";
		$this->addTextArea("custom_css",array(
			"name" => "custom_css",
			"value" => @file_get_contents($css)
		));
		
		//favicon
		$favicon = "content/" . SOYCMS_LOGIN_SITE_ID . ".ico";
		$this->addImage("favicon",array(
			"src" => SOYCMS_ROOT_URL . $favicon,
			"visible" => file_exists(SOYCMS_COMMON_DIR . $favicon)
		));
		
		//カスタムサムネイル
		$thumb = "content/thumb_" . SOYCMS_LOGIN_SITE_ID . ".png";
		$this->addImage("thumbnail_img",array(
			"src" => SOYCMS_ROOT_URL . $thumb,
			"visible" => file_exists(SOYCMS_ROOT_DIR . $thumb)
		));
		
		//footer 
		$footer_default = <<<HTML
<div class="navi">
	<ul>
		<li><a href="http://www.soycms2.net/document/" target="_blank">ドキュメント(マニュアル)</a></li> 
		<li><a href="http://twitter.com/SOYCMS2_dev" target="_blank">SOY CMS2 Developer Team Twitter</a></li>
		<li><a href="http://www.facebook.com/SOYCMS" target="_blank">SOY CMS2 Facebook</a></li>
		<li class="last"><a href="http://www.soycms2.net/feedback/" target="_blank">フィードバックする</a></li>
	</ul>
</div>
HTML;
		$this->addTextArea("parts_footer",array(
			"name" => "parts_footer",
			"text" => SOYCMS_DataSets::get("parts.footer",$footer_default)
		));
		
	}
	
	/**
	 * カスタムアイコン用
	 */
	function getCustomIconPath($type = "header"){
		$img = "content/${type}_icon_" . SOYCMS_LOGIN_SITE_ID . ".png";
		return $img;
	}
	
	function getConfig(){
		//SOYCMS_DataSets::delete("config_custom");
		return SOYCMS_DataSets::get("config_custom",array(
			"header_icon" => 0,
			"popup_icon" => 0,
			"cp_theme" => "gray"
		));
	}
}