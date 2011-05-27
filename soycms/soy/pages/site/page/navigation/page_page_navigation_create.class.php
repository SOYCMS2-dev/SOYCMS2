<?php
/**
 * @title ナビゲーション管理
 */
class page_page_navigation_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Navigation"])){
			SOY2::cast($this->navigation,(object)$_POST["Navigation"]);
		}
		
		$tmp = SOYCMS_Navigation::load($this->navigation->getId());
			
		if(!$tmp){
		
			if(isset($_POST["NavigationItem"])){
				$items = array();
				foreach($_POST["NavigationItem"] as $key => $array){
					$item = SOY2::cast("SOYCMS_NavigationItem",(object)$array);
					$items[] = $item;
				}
				$this->navigation->setItems($items);
			}
			
			if(isset($_POST["NewNavigationItem"]) && isset($_POST["add_item"])){
				$items = $this->navigation->getItems();
				$item = SOY2::cast("SOYCMS_NavigationItem",(object)$_POST["NewNavigationItem"]);
				$items[] = $item;
				$this->navigation->setItems($items);
			}
			
			if(isset($_POST["next"])){
				if(!$this->navigation->getTemplate()){
					$this->navigation->setTemplate("新しいナビゲーション");
				}
				
				$this->navigation->save();
				
				//自動生成
				$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
				$logic->autoAppend($this->navigation);
				$logic->updateItemOrder($this->navigation);
				$this->navigation->save();
				
				$this->jump("/page/navigation/detail?id=" . $this->navigation->getId() . "?created");
			}
			
		
		}
		
		$this->error = true;
		
		
	}
	
	private $navigation;
	private $error = false;
	
	function prepare(){
		$this->navigation = new SOYCMS_Navigation();
		
		if(isset($_GET["id"])){
			$old = SOYCMS_Navigation::load($_GET["id"]);
			
			if($old){
				$html = $old->loadTemplate();
				$old->setId($_GET["id"] . "_copy");
				$this->navigation = $old;
				
				$this->navigation->setTemplate($html);
				
			}
		}
		
		parent::prepare();
	}

	function page_page_navigation_create(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.NavigationForm",array(
			"navigation" => $this->navigation
		));
		
		$this->addModel("create_error",array(
			"visible" => $this->error
		));
		
	}
}