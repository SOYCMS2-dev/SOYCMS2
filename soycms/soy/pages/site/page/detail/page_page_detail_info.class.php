<?php
/**
 * @title ディレクトリの設定 > テンプレート設定
 */
class page_page_detail_info extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;
	private $mode = "edit";

	function page_page_detail_info($args){
		
		$this->id = @$args[0];
		$this->page = @$args[1];
		if(count($args)>2)$this->mode = $args[2];
		
		WebPage::WebPage();
			
		$this->buildPage();
		$this->bulidForm();
		$this->buildTemplateInfo();
		
		$this->addModel("is_edit_page",array(
			"visible" => ($this->mode == "edit")
		));
	}
	
	function buildPage(){
		$page = $this->page;
		
		$this->addLabel("page_name",array("text" => $page->getName()));
		
		$link = soycms_get_page_url($page->getUri());
		$this->addLink("page_link",array("link" => $link));
		$this->addLabel("page_link_text",array(
			"text" => $link
		));
		
		$link = soycms_get_page_url($page->getUri()) . "?dynamic&" . soycms_get_ssid_token();
		$this->addLink("dynamic_link",array("link" => $link));
		$this->addLabel("dynamic_link_text",array(
			"text" => "ダイナミック編集"
		));
		
		
		
		$this->buildEntryLink($page);
		
		$this->addLabel("page_type_text",array("text" => $page->getTypeText()));
		$this->createAdd("create_date","_class.component.SimpleDateLabel",array("date" => $page->getCreateDate()));
		$this->createAdd("update_date","_class.component.SimpleDateLabel",array("date" => $page->getUpdateDate()));
		
		$this->addLabel("create_user_name",array("text" => "-"));
		
		
	}
	
	function buildEntryLink($page){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		if($page->isDirectory()){
			$link = soycms_create_link("/entry/list/" . $page->getId());
			$count = $dao->countByDirectory($page->getId());
			$text = $page->getName() . "(".$count.")";
		}else{
			$entry = $dao->getByDirectory($page->getId());
			if(count($entry) != 1){
				foreach($entry as $obj){
					$obj->delete();
				}
				$entry = new SOYCMS_Entry();
				$entry->setDirectory($page->getId());
				$entry->setTitle($page->getName());
				$entry->setUri("");
				$entry->save();
			}else{
				$entry = $entry[0];
			}
			
			$link = soycms_create_link("/entry/detail/" . $entry->getId());
			$text = $entry->getTitle();
		}
		
		
		$this->addLink("entry_link",array(
			"link" => $link
		));
		
		$this->addLabel("entry_link_text",array(
			"text" => $text
		));
		
		/* グループ関連 */
		$loginSession = SOY2Session::get("site.session.SiteLoginSession");
		
		$this->addLink("group_config_link",array(
			"link" => soycms_create_link("/page/detail/group/" . $this->id),
		));
		
		$this->addModel("group_config_link_wrap",array(
			"visible" => ($loginSession->hasRole("super"))
		));
	}
	
	function buildTemplateInfo(){
		$this->addForm("template_config_form");
		
		//templates
		$templates = SOYCMS_Template::getListByType($this->page->getPageObject()->getTemplateType());
		
		$options = array();
		foreach($templates as $key => $template){
			$options[$template->getId()] = $template->getName();
		}
		
		$templateId = $this->page->getTemplate();
		
		$this->addLabel("template_name",array(
			"text" => (isset($templates[$templateId])) ? $templates[$templateId]->getName() : $templateId . "\n"
		));
		
		$this->addLink("template_link",array(
			"link" => (isset($templates[$templateId])) ? soycms_create_link("/page/template/detail?id=") . $templateId . "#tpl_config" : ""
		));
		
		
		$this->addTextArea("template_content",array(
			"name" => "Template[template]",
			"value" => (isset($templates[$templateId])) ? $templates[$templateId]->loadTemplate() : "\n"
		));
		
		$this->addTextArea("template_property",array(
			"name" => "Template[property]",
			"value" => (isset($templates[$templateId])) ? @file_get_contents($templates[$templateId]->getPropertyFilePath()) : "\n"
		));
		
		$this->createAdd("template_list","_class.list.TemplateList",array(
			"inputName" => "Page[template]",
			"list" => $templates,
			"selected" => $templateId
		));
		
		$this->addLabel("layout_config",array(
			"html" => (isset($templates[$templateId])) ? json_encode($templates[$templateId]->getLayout()) : "{}" 
		));
		
		$this->addLink("preview_link",array(
			"link" => SOYCMS_SITE_URL . "?template_preview=" . $templateId . "&" . soycms_get_ssid_token()
		));
		
		//tab
		$this->addModel("type_error",array(
			"visible" => $this->page->getType() == ".error"
		));
	}
	
	
	function bulidForm(){
		
	}
	
	function getLayout(){
		return "blank";
	}
}