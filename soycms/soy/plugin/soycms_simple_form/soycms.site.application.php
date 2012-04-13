<?php
/*
 * 簡易問い合わせフォーム
 */
class SOYCMS_SimpleContactFormExtension extends SOYCMS_SiteApplicationExtension{
	
	function SOYCMS_SimpleContactFormExtension(){
		SOY2::imports("*", dirname(__FILE__) . "/src/");
		
		if(isset($_GET["sended"])){
			$this->mode = "complete";
		}
	}
	
	private $mode = "form";
	private $contact = array();
	private $builder = null;
	
	/**
	 * 公開側フォーム
	 */
	function getForm($htmlObj,$page){
		
		//値の準備
		$uri = soycms_get_page_url($page->getUri()) . "#form";
		$config = self::getConfig($page);
		$items = $config["items"];
		
		
		$session_values = @$_SESSION["_soycms_simple_form"];
		
		switch($this->mode){
			case "confirm":
				$html = $config["confirm_html"];
				$values = $_POST;
				$values["_go_next"] = time();
				$values["_go_next_token"] = md5($values["_go_next"] . "_soycms_simple_form");
				$html = $this->buildForm($this->mode,$uri,$html,$items,$values);
				
				break;
				
			case "complete":
				$html = $config["complete_html"];
				$html = $this->buildForm($this->mode,$uri,$html,$items,array("mode" => "complete"),$session_values);
				return $html;
				break;
				
			case "form":
			default:
				$html = $config["form_html"];
				$html = $this->buildForm($this->mode,$uri,$html,$items);
				break;
		}
		
		unset($_SESSION["_soycms_contact_form"]);
		
		return $html;
		
	}
	
	/**
	 * 公開側フォーム POST時
	 */
	function doPost($htmlObj,$page){
		$config = self::getConfig($page);
		$items = $config["items"];
		
		//完了画面から戻る場合
		if(isset($_POST["mode"]) && $_POST["mode"] == "complete" && isset($_POST["back"])){
			$url = soycms_get_page_url($page->getUri());
			SOY2PageController::redirect($url);
			exit;
		}
		
		if($this->validate($items,$_POST)){
			
			if($config["is_show_confirm"]){
				$this->mode = "confirm";
				
				if(isset($_POST["back"]) || isset($_POST["back_x"])){
					$this->mode = "form";
					return;
				}
				
				if(isset($_POST["_go_next"]) && md5($_POST["_go_next"] . "_soycms_simple_form") == $_POST["_go_next_token"]){
					$this->doSubmit($config,$_POST,$page);
					exit;
				}
				
			}else{
				$this->doSubmit($config,$_POST,$page);
				exit;
			}
		}
		
	}
	
	/**
	 * 送信実行
	 */
	function doSubmit($config,$values,$page){
		
		//セッションに値を保存
		$_SESSION["_soycms_simple_form"] = $values;
		
		//メールの送信
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		
		$builder = $this->getFormBuilder($config["form_html"]);
		$builder->setValues($values);
		
		
		//ユーザへ送信する宛先を取得
		$target = @$config["confirm_mail_target"];
		if(!$target){
			$target = "mail";
		}
		$sendto = @$values[$target];
		
		
		$config["mail_title"] =$builder->convertValues($config["mail_title"],false);
		$config["mail_body"] =$builder->convertValues($config["mail_body"],false);
		
		$config["admin_mail_title"] =$builder->convertValues($config["admin_mail_title"],false);
		$config["admin_mail_body"] =$builder->convertValues($config["admin_mail_body"],false);
		
		//送信実行
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		$conf = $logic->getServerConfig();
		
		if(isset($config["from_addr"]) && strlen($config["from_addr"])){
			$sender = $logic->getSender();
			$sender->setFrom($config["from_addr"]);
			$logic->setSender($sender);
		}
		
		$logic->send(
			$conf->getFromMailAddress(),
			@$config["admin_mail_title"],
			@$config["admin_mail_body"]
		);
		$address = explode(",",@$config["admin_addr"]);
		foreach($address as $addr){
			$logic->send(
					$addr,
					@$config["admin_mail_title"],
					@$config["admin_mail_body"]
			);
		}
		
		if(@$config["is_send_confirm_mail"] && $sendto){
			$logic->send(
				$sendto,
				@$config["mail_title"],
				@$config["mail_body"]
			);
		}
		
		$url = soycms_get_page_url($page->getUri()) ."?sended&" . session_name() . "=" . session_id();
		SOY2PageController::redirect($url);
	}

	
	/**
	 * 設定フォーム
	 */
	function getConfigForm($page){
		PluginManager::import("soycms_simple_form","src.pages.SOYCMS_SimpleContactFormExtension_ConfigPage");
		$page = SOY2HTMLFactory::createInstance("SOYCMS_SimpleContactFormExtension_ConfigPage",array(
			"arguments" => array($page)
		));
		return $page->getObject();
	}	
	
	
	/**
	 * 設定値を取得する
	 */
	public static function getConfig($page){
		return SOYCMS_ContactFormHelper::getConfig($page);
	}
	
	/**
	 * フォームを作る
	 */
	function buildForm($mode,$action,$html,$items,$array = array(),$values = null){
		$builder = $this->getFormBuilder($html);
		$builder->setItems($items);
		
		if($values){
			$builder->setValues($values);
		}
	
		$array = array_merge($array,$builder->getValues());
		
		$values = array("soycms_simple_form" => $this->getVersion());
		foreach($array as $key => $value){
			$values[$key] = $value;
		}
		$builder->setAction($action);
		
		if($mode == "form"){
			$values = array();
		}
		
		$html = $builder->getForm($html,$values);
		
		return $html;
	}
	
	function validate($items,$values){
		$builder = $this->getFormBuilder();
		$builder->setItems($items);
		return $builder->validate($items,$values);
	}
	
	function getFormBuilder($html = null){
		if(!$this->builder){
			$this->builder = new SOYCMS_SimpleFormBuilder($html);
		}
		return $this->builder;
	}
	
	function getVersion(){
		return "1.0.0";
	}		
}

PluginManager::extension("soycms.site.application","soycms_simple_form","SOYCMS_SimpleContactFormExtension");

