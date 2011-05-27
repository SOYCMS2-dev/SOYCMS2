<?php
/**
 * @title テンプレートの作成
 */
class page_page_template_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Template"])){
			SOY2::cast($this->template,(object)$_POST["Template"]);
		}
		
		$options = array();
		$options["is_overwrite"] = @$_POST["is_overwrite"];
		
		//既存のテンプレートと同じIDだった場合はエラー
		$id = $this->template->getId();
		$load = SOYCMS_Template::load($id);
		
		if(!$load){
		
		
			if(isset($_POST["TemplateItem"])){
				$items = array();
				foreach($_POST["TemplateItem"] as $key => $array){
					$item = SOY2::cast("SOYCMS_TemplateItem",(object)$array);
					$items[] = $item;
				}
				$this->template->setItems($items);
			}
			
			if(isset($_POST["next"])){
				
				//生成作業のため1回保存
				$this->template->save();
				
				$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
				
				if(isset($_POST["template_select"])){
					$t_id = $_POST["template_select"];
					$templates = SOYCMS_Template::getList();
					if(isset($templates[$t_id])){
						$items = $templates[$t_id]->getItems();
						$layout = $templates[$t_id]->getLayout();
						$properties = @file_get_contents($templates[$t_id]->getPropertyFilePath());
						
						$this->template->setItems($items);
						$this->template->setLayout($layout);
						$this->template->setProperty($properties);
					}
				}
				
				if(count($this->template->getLayout())<1){
					$layout = $logic->checkLayoutByTemplateContent($this->template);
					$this->template->setLayout($layout);
					$this->template->save();
				}
				
				//自動生成
				$logic->autoAppend($this->template,$options);
				$logic->updateItemLayout($this->template);
				$this->template->save();
				
				//先頭に追加
				$orders = SOYCMS_DataSets::get("template.order",array());
				if(!is_array($orders))$orders = array();
				array_unshift($orders,$this->template->getId());
				SOYCMS_DataSets::put("template.order",$orders);
				
				
				$this->jump("/page/template/check?id=" . $this->template->getId() . "&created");
			}
		}
		
		$this->error = true;
		$_GET["failed"] = true;
		
	}
	
	private $template;
	private $id;
	private $error = false;
	
	function prepare(){
		$this->template = new SOYCMS_Template();
		$this->template->setTemplate("<p>テンプレートのHTMLを貼り付けてください</p>");
		
		if(isset($_GET["id"]) && ($template = SOYCMS_Template::load($_GET["id"]))){
			$this->id = $_GET["id"];
			$template->loadTemplate();
			SOY2::cast($this->template,$template);
				
		}
		
		$this->template->setId("tpl_" . $this->id. date("Ymd"));
		
		parent::prepare();
	}

	function page_page_template_create(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.TemplateForm",array(
			"template" => $this->template
		));
		
		$templates = SOYCMS_Template::getList();
		
		$this->addSelect("parent_template_select",array(
			"options" => $templates,
			"property" => "name",
			"name" => "template_select",
			"selected" => $this->id
		));
		
		$this->addModel("create_error",array(
			"visible" => $this->error
		));
	}
}