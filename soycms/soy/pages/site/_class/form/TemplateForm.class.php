<?php

class TemplateForm extends HTMLForm{
	
	private $template;

	function execute() {
		
		$template = $this->getTemplate();
		$this->buildForm($template);
		
		
		parent::execute();
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($template){
		
		//テンプレート名
		$this->addInput("template_name",array(
			"name" => "Template[name]",
			"value" => $template->getName()
		));
		
		$this->addInput("template_id",array(
			"name" => "Template[id]",
			"value" => $template->getId()
		));
		
		$this->addSelect("template_type",array(
			"name" => "Template[type]",
			"options" => SOYCMS_Template::getTypes(),
			"selected" => $template->getType()
		));
		
		$this->createAdd("template_type_list","TemplateForm_TypeSelectList",array(
			"name" => "Template[type]",
			"list" => SOYCMS_Template::getTypes(),
			"selected" => $template->getType()
		));
		
		$this->addLabel("template_type_text",array(
			"text" => $template->getTypeText()
		));
		
		$this->addTextArea("template_content_area",array(
			"name" => "Template[template]",
			"value" => $template->getTemplate()
		));
		
		//アイテム一覧
		$this->createAdd("template_item_manager","_class.component.TemplateItemComponent",array(
			"templateId" => $template->getId(),
			"layoutArray" => $template->getLayout(),
			"items" => $template->getItems(),
			"type" => $template->getType()
		));
		
		$this->addInput("template_color",array(
			"name" => "Template[borderColor]",
			"value" => $template->getBorderColor(),
			"style" => "background-color:" . $template->getBorderColor()
		));
		
	}

	function getTemplate() {
		if(!$this->template){
			$this->template = new SOYCMS_Template();
			$this->template->setId("tpl_" . date("Ymd"));
		}
		return $this->template;
	}
	function setTemplate($template) {
		$this->template = $template;
	}
}

class TemplateForm_TypeSelectList extends HTMLList{
	
	private $name;
	private $selected = "detail";
	
	function populateItem($entity,$key){
		$tmpl = "tmpl_layout_" . str_replace(".","",$key) . ".gif";
		$this->addImage("template_type_image",array(
			"src" => SOYCMS_ROOT_URL . "common/img/{$tmpl}",
		));
		
		$this->addCheckbox("template_type_radio",array(
			"name" => "Template[type]",
			"value" => $key,
			"label" => $entity,
			"selected" => $this->selected == $key
		));
	}
	

	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}

?>