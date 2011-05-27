<?php
/**
 * @title メール文面の設定
 */
class page_user_workflow_mail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["save"]) && isset($_POST["Data"])){
			foreach($_POST["Data"] as $key => $value){
				SOYCMS_DataSets::put($key,$value);
			}
		}
		
		
		$this->jump("/user/workflow/mail?updated");
		
	}

	function page_user_workflow_mail(){
		WebPage::WebPage();
		
		$this->addUploadForm("form");
		$this->buildForm();
	}
	
	function buildForm(){
		$this->addInput("review_mail_title",array(
			"name" => "Data[mail.review.title]",
			"value" => SOYCMS_DataSets::get("mail.review.title","メッセージが送信されました")
		));
		
		$this->addTextArea("review_mail_header",array(
			"name" => "Data[mail.review.header]",
			"value" => SOYCMS_DataSets::get("mail.review.header","以下の内容で申請があります")
		));
		
		$this->addTextArea("review_mail_footer",array(
			"name" => "Data[mail.review.fooer]",
			"value" => SOYCMS_DataSets::get("mail.review.fooer",
					"--\n" . SOYCMS_DataSets::get("site_name","サイト名") . "\n" .
					SOYCMS_SITE_URL
			)
		));
		
		
		
	}
}