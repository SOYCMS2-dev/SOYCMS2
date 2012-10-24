<?php

class SOYCMS_ContactFormHelper {
	
	public static function saveConfig($page,$config){
		$items = array();
		foreach($config["items"] as $array){
			$items[$array->getId()] = $array;
		}
		$items = serialize($items);
		$config["items"] = base64_encode($items);
		
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
				"admin_addr" => "",
				"is_send_confirm_mail" => true,
				"confirm_mail_input" => "your-mail",
				"is_show_confirm" => true,
				"form_html" => file_get_contents(dirname(__FILE__) . "/template/form.html"),
				"confirm_html" => file_get_contents(dirname(__FILE__) . "/template/confirm.html"),
				"complete_html" => file_get_contents(dirname(__FILE__) . "/template/complete.html"),
				"admin_mail_title" => "【問い合わせがあります】from: #name# (#mail#)",
				"admin_mail_body" => file_get_contents(dirname(__FILE__) . "/template/admin_mail.html"),
				"mail_title" => "お問い合わせありがとうございます。",
				"mail_body" => file_get_contents(dirname(__FILE__) . "/template/client_mail.html"),
			);
		}
		
		if(!isset($config["items"]) || empty($config["items"])){
			$config["items"] = self::getDefaultItems();
		}
		
		if(is_string($config["items"])){
			$items = @unserialize(base64_decode($config["items"]));
			if($items === false)$items = unserialize($config["items"]);
			$config["items"] = $items;
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