<?php

class NavigationForm extends HTMLForm{
	
	private $navigation;

	function execute() {
		
		$navigation = $this->getNavigation();
		$this->buildForm($navigation);
		
		
		parent::execute();
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($navigation){
		
		//ID
		$this->addInput("navigation_id",array(
			"name" => "Navigation[id]",
			"value" => $navigation->getId()
		));
		$this->addLabel("navigation_id_text",array(
			"text" => $navigation->getId()
		));
		
		//テンプレート名
		$this->addInput("navigation_name",array(
			"name" => "Navigation[name]",
			"value" => $navigation->getName()
		));
		
		//説明
		$this->addTextArea("navigation_description",array(
			"name" => "Navigation[description]",
			"text" => $navigation->getDescription()
		));
		
		//テンプレート本体
		$this->addTextArea("navigation_content",array(
			"name" => "Navigation[template]",
			"text" => ($navigation->getTemplate()) ? $navigation->getTemplate() : $navigation->loadTemplate()
		));
		$this->addTextArea("navigation_content_area",array(
			"name" => "Navigation[content]",
			"text" => ($navigation->getTemplate()) ? $navigation->getTemplate() : $navigation->loadTemplate()
		));
		
		//アイテム一覧
		$this->createAdd("navigation_item_manager","_class.component.TemplateItemComponent",array(
			"layoutArray" => $navigation->getLayout(),
			"items" => $navigation->getItems(),
			"navigationId" => $navigation->getId(),
			"mode" => "navigation"
		));
		
	}

	function getNavigation() {
		if(!$this->navigation){
			$this->navigation = new SOYCMS_Navigation();
		}
		return $this->navigation;
	}
	function setNavigation($navigation) {
		$this->navigation = $navigation;
	}
}
?>