<?php

class SOYCMS_SimpleContactFormExtension_ConfigPage extends HTMLPage{
	
	private $pageObj;

	function SOYCMS_SimpleContactFormExtension_ConfigPage($args) {
		$this->pageObj = $args[0];
		
		HTMLPage::HTMLPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLink("create_link",array(
			"link" => soycms_create_link("ext/soycms_simple_form?id=" . $this->pageObj->getId() . "&mode=create")
		));
		$this->addLink("sample_code_link",array(
			"link" => soycms_create_link("ext/soycms_simple_form?id=" . $this->pageObj->getId() . "&mode=sample&layer")
		));
		
		
		//サーバー管理者のメールアドレスが設定されているかどうか
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		$conf = $logic->getServerConfig();
		$adminMail = $conf->getFromMailAddress();
		
		$this->addModel("blank_admin_mailaddress",array(
			"visible" => empty($adminMail)
		));
	}
	
	function buildForm(){
		$config = $this->getConfig();
		$items = $config["items"];
		if(!is_array($items))$items = array();
		
		$this->createAdd("field_list","ContactFormFieldList",array(
			"list" => (is_array($config["items"])) ? $config["items"] : array(),
			"pageId" => $this->pageObj->getId(),
			"html" => $config["form_html"]
		));
		
		$this->addInput("from_addr",array(
			"name" => "object[config][from_addr]",
			"value" => @$config["from_addr"]
		));
		
		$this->addInput("admin_addr",array(
			"name" => "object[config][admin_addr]",
			"value" => @$config["admin_addr"]
		));
		
		$this->addCheckbox("is_send_confirm_mail",array(
			"name" => "object[config][is_send_confirm_mail]",
			"elementId" => "is_send_confirm_mail",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (@$config["is_send_confirm_mail"] == 1)
		));
		
		$this->addInput("admin_mail_title",array(
			"name" => "object[config][admin_mail_title]",
			"value" => @$config["admin_mail_title"]
		));
		
		$this->addTextArea("admin_mail_body",array(
			"name" => "object[config][admin_mail_body]",
			"value" => @$config["admin_mail_body"]
		));
		
		$this->addInput("mail_title",array(
			"name" => "object[config][mail_title]",
			"value" => @$config["mail_title"]
		));
		
		$this->addTextArea("mail_body",array(
			"name" => "object[config][mail_body]",
			"value" => @$config["mail_body"]
		));
		
		$this->addCheckbox("is_show_confirm",array(
			"name" => "object[config][is_show_confirm]",
			"elementId" => "is_show_confirm",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (@$config["is_show_confirm"] == 1)
		));
		
		$this->addTextArea("form_html",array(
			"name" => "object[config][form_html]",
			"value" => @$config["form_html"]
		));
		$this->addTextArea("confirm_html",array(
			"name" => "object[config][confirm_html]",
			"value" => @$config["confirm_html"]
		));
		$this->addTextArea("complete_html",array(
			"name" => "object[config][complete_html]",
			"value" => @$config["complete_html"]
		));
		
		$mails = array();
		foreach($items as $key => $field){
			if($field->getType() == "mailaddress"){
				$mails[$key] = $key . ":" . $field->getName();
			}
		}
		
		$this->addSelect("confirm_mail_target_select",array(
			"name" => "object[config][confirm_mail_target]",
			"selected" => @$config["confirm_mail_target"],
			"options" => $mails,
			"property" => "name"
		));
		
		$this->addInput("field_items",array(
			"name" => "object[config][items]",
			"value" => serialize(@$config["items"])
		));
	}
	
	function getConfig(){
		$config = SOYCMS_ContactFormHelper::getConfig($this->pageObj);
		
		return $config;
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/".get_class($this).".html";
	}
}

class ContactFormFieldList extends HTMLList{
	
	private $id;
	private $link;
	private $html;
	
	function setPageId($id){
		$this->id = $id;
	}
	
	function setHtml($html){
		$this->html = $html;
	}
	
	function init(){
		$this->link = soycms_create_link("ext/soycms_simple_form?id=" . $this->id);
		uksort($this->list,array($this,"sortItems"));
	}
	
	function sortItems($a,$b){
		if(strpos($this->html,"cms:id=\"contact_{$a}\"") === false){
			return 1;
		}
		
		return (strpos($this->html,"cms:id=\"contact_{$a}\"") < strpos($this->html,"cms:id=\"contact_{$b}\""))
				? -1 : 1;
	}
	
	function populateItem($entity,$key){
		$require = ($entity->getRequire()) ? "<span class=\"field-require\">*</span> " : "";
		$exists = (strpos($this->html,"contact_" . $key) !== false) ? "" : " <span class=\"field-error\">?</span>";
		
		$this->addLabel("list_field_name",array("text" => $entity->getName()));
		$this->addLabel("list_field_id",array("html" => $require . $entity->getId() . $exists));
		$this->addLink("list_field_detail_link",array(
			"link" => $this->link . "&field=" . $entity->getId()
		));
		
		$this->addLabel("list_field_type",array("text" => $entity->getTypeText()));
		$this->addActionLink("field_remove_link",array(
			"link" => $this->link . "&field=" . $entity->getId() . "&mode=remove"
		));
		
	}
	
}
?>