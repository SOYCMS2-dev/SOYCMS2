<?php

class SOYCMS_ContactFormHelper {
	
	public static function saveConfig($page,$config){
		$config["items"] = serialize($config["items"]);
		
		$pageObj = $page->getPageObject();
		$pageObj->setConfig($config);
		$pageObj->save();
		
	}
	
	/**
	 * 設定値を取得する
	 */
	public static function getConfig($page){
		//全部OKだったらメールを送信する
		$config = $page->getPageObject()->getConfig();
		
		//初期化されているかチェック
		if(empty($config) || !isset($config["mail_title"]) || strlen($config["mail_title"]) < 1){
			$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
			$conf = $logic->getServerConfig();
			$config = array(
				"items" => self::getDefaultItems(),
				"admin_addr" => $conf->getFromMailAddress(),
				"is_send_confirm_mail" => true,
				"confirm_mail_input" => "your-mail", 
				"is_show_confirm" => true,
				"form_html" => file_get_contents(dirname(__FILE__) . "/template/form.html"),
				"confirm_html" => file_get_contents(dirname(__FILE__) . "/template/confirm.html"), 
				"complete_html" => file_get_contents(dirname(__FILE__) . "/template/complete.html"),
				"admin_mail_title" => "【問い合わせがあります】from: #name# (#mail#)",
				"admin_mail_body" => "問い合わせがあります\r\n" .
						"お名前：#name# 様\r\n" .
						"メールアドレス：#mail#\r\n\r\n" .
						"件名：#title#\r\n\r\n" .
						"#body#",
				"mail_title" => "お問い合わせありがとうございます。",
				"mail_body" => 
						"#name#様\r\n\r\n今回は○○にお問い合わせありがとうござます。\r\n近日中に返答いたします。\r\n" .
						"\r\n" .
						"※このメールはシステムによる自動返信メールです。直接このメールに返信は出来ませんのでご注意ください。\r\n" .
						"\r\n\r\n株式会社○○\r\nTEL:XXX-XXX-XXX\r\n住所:東京都千代田区",
			);
		}
		
		if(!isset($config["items"]) || empty($config["items"])){
			$config["items"] = self::getDefaultItems();
		}
		
		if(is_string($config["items"])){
			$config["items"] = @unserialize($config["items"]);
		}
		
		return $config;
	}
	
	public static function getDefaultItems(){
		$array = array();
		
		$obj = new SOYCMS_ContactFormField();
		$obj->setId("title");
		$obj->setType("input");
		$obj->setName("件名");
		$obj->setRequire(false);
		$array["title"] = $obj;
		
		$obj = new SOYCMS_ContactFormField();
		$obj->setId("name");
		$obj->setType("input");
		$obj->setName("お名前");
		$obj->setRequire(true);
		$array["name"] = $obj;
		
		$obj = new SOYCMS_ContactFormField();
		$obj->setId("mail");
		$obj->setType("mailaddress");
		$obj->setName("メールアドレス");
		$obj->setRequire(true);
		$obj->setOption("confirm",1);
		$array["mail"] = $obj;
		
		$obj = new SOYCMS_ContactFormField();
		$obj->setId("body");
		$obj->setType("textarea");
		$obj->setName("問い合わせ内容");
		$obj->setRequire(true);
		$array["body"] = $obj;
		
		return $array;
	}

}
?>