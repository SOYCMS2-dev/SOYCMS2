<?php
/**
 * @title テンプレートを一括で設定
 */
class page_page_template_pages extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["TogglePage"])){
			$pageObj = SOY2DAOFactory::create("SOYCMS_PageDAO");
			
			foreach($_POST["TogglePage"] as $pageId){
				$page = $pageObj->getById($pageId);
				$page->setTemplate($this->id);
				$page->save();
			}
		}
		
		
		$this->jump("/page/template/pages?id=" . $this->id . "&updated");
	}
	
	private $id;
	private $template;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->template = SOYCMS_Template::load($this->id);
		
		parent::prepare();
	}

	function page_page_template_pages(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addLabel("template_name_text",array("text" => $this->template->getName()));
		
		
		$this->createAdd("page_list","TemplateMap_PageTreeComponent",array(
			"templateObj" => $this->template,
			"templates" => $this->getTemplates()
		));
	}
	
	function getTemplates(){
		$templates = SOYCMS_Template::getList();
		$res = array();
		
		foreach($templates as $template){
			$res[$template->getId()] = $template->getName();
		}
		
		return $res;
	}
	
	function getLayout(){
		return "layer.php";
	}
}
SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");
class TemplateMap_PageTreeComponent extends PageTreeComponent{
	
	private $templates = array();
	private $templateObj = null;
	
	function populateItem($entity,$key,$depth,$isLast){
		
		$this->addModel("template_toggle_check_wrap",array(
			"visible" => ($this->templateObj->getType() == $entity->getType()
				&& $this->templateObj->getId() != $entity->getTemplate()
				)
		));
		
		$this->addCheckbox("template_toggle_check",array(
			"name" => "TogglePage[]",
			"value" => $entity->getId(),
			"label" => $this->templateObj->getName() . "に変更する"
		));
		
		$this->addLabel("current_template_name",array(
			"text" => @$this->templates[$entity->getTemplate()]
		));
		
		return parent::populateItem($entity,$key,$depth,$isLast);
	}
	
	
	function getTemplateObj() {
		return $this->templateObj;
	}
	function setTemplateObj($templateObj) {
		$this->templateObj = $templateObj;
	}
	function getTemplates() {
		return $this->templates;
	}
	function setTemplates($templates) {
		$this->templates = $templates;
	}
}