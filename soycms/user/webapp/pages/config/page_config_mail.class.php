<?php

class page_config_mail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["config"])){
			foreach($_POST["config"] as $key => $value){
				SOYCMS_DataSets::put("plus.user.mail." . $key, $value);
			}
		}
		
		
		$this->jump("config/mail?updated");
		
	}

	function page_config_mail() {
		WebPage::WebPage();
		$this->addForm("form");
		
		$this->createAdd("group_list","page_config_mail_GropList",array(
			"list" => $this->getGroups(),
		));
		
		
		list($title,$content) = $this->getDefaultMailContent("widhdraw_notify");
		$this->addInput("withdraw_notify_mail_title",array(
			"name" => "config[widhdraw_notify.title]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.widhdraw_notify.title",$title)
		));
		$this->addTextArea("withdraw_notify_mail",array(
			"name" => "config[widhdraw_notify.body]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.widhdraw_notify.body",$content)
		));
		
		list($title,$content) = $this->getDefaultMailContent("register_notify");
		$this->addInput("withdraw_notify_mail_title",array(
			"name" => "config[register_notify.title]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.register_notify.title",$title)
		));
		$this->addTextArea("withdraw_notify_mail",array(
			"name" => "config[register_notify.body]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.register_notify.body",$content)
		));
	}
	
	function getGroups(){
		$groups = SOY2DAO::find("Plus_Group");
		$res = array();
		foreach($groups as $obj){
			//新規登録をするグループだけ
			if($obj->getConfigure("register")){
				$res[$obj->getGroupId()] = $obj;
			}
		}
		return $res;
	}
	
	function getDefaultMailContent($type){
		$title = "";
		$content = "";
		
		switch($type){
			case "register_notify":
				$content = "登録されました";
				break;
			case "widthdraw_notify":
				$title = "退会しました";
				break;
		}
		
		return array($title,$content);
		
	}
}

class page_config_mail_GropList extends HTMLList{
	
	function populateItem($entity,$key){
		
		$this->addLabel("group_name",array(
			"text" => $entity->getName()
		));
		
		list($title,$content) = $this->getDefaultMailContent("register",$entity);
		$this->addInput("register_mail_title",array(
			"name" => "config[{$key}.register.title]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.register.title",$title)
		));
		$this->addTextArea("register_mail",array(
			"name" => "config[{$key}.register.body]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.register.body",$content)
		));
		
		list($title,$content) = $this->getDefaultMailContent("complete",$entity);
		$this->addInput("complete_mail_title",array(
			"name" => "config[{$key}.complete.title]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.complete.title",$title)
		));
		$this->addTextArea("complete_mail",array(
			"name" => "config[{$key}.complete.body]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.complete.body",$content)
		));
		
		list($title,$content) = $this->getDefaultMailContent("withdraw",$entity);
		$this->addInput("withdraw_mail_title",array(
			"name" => "config[{$key}.withdraw.title]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.withdraw.title",$title)
		));
		$this->addTextArea("withdraw_mail",array(
			"name" => "config[{$key}.withdraw.body]",
			"value" => SOYCMS_DataSets::get("plus.user.mail.{$key}.withdraw.body",$content)
		));
		
	}
	
	function getDefaultMailContent($type,$group){
		$title = "";
		$content = "";
		
		switch($type){
			case "register":
				$title = "仮登録確認メール";
				$content = <<<MAIL_CONTENT
確認コード：#CODE#
次のURLにアクセスしてください:#CODE_URL#
MAIL_CONTENT;
				break;
			case "complete":
				$title = "登録が完了しました";
				$content = "登録ありがとうございます\n" .
						"下記URLよりログインしてください\n" .
						"\n" .
						"ログインURL: #LOGIN_URL#";
				break;
			case "withdraw":
				$title = "退会手続きが完了しました";
				$content = "ご登録ありがとうございました\nまたのご利用をお待ちしております。";
				break;
			default:
			
				break;
		}
		
		return array($title,$content);
		
	}
}
?>