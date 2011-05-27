<?php
/**
 * @title ディレクトリ追加
 */
class page_page_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		$page = SOY2::cast("SOYCMS_Page",(object)$_POST["Page"]);
		
		if(isset($_POST["uri_prefix"])){
			$page->setUri($_POST["uri_prefix"] . $page->getUri());
		}
		
		//テンプレートの指定
		if(isset($_POST["directory_template"])){
			$page->setType("detail");
			
			//テンプレートIDを指定の場合
			if(strlen($_POST["directory_template"]) > 0){
				$page->setTemplate($_POST["directory_template"]);
			}
		
		//ページテンプレートを指定している場合
		}else if(isset($_POST["page_template"]) && strlen($_POST["page_template"])>0){
			$template = SOYCMS_Template::load($_POST["page_template"]);
			if($template){
				$page->setTemplate($_POST["page_template"]);
				$page->setType($template->getType());
			}
			$template = null;
		}
		
		if(isset($_POST["object_config"])){
			$config = soy2_unserialize(base64_decode($_POST["object_config"]));
			$obj = $page->getObject();
			SOY2::cast($obj,$config);
			$page->setObject($obj);
		}
		
		//値をチェックして作成
		if($page->check()){
			$logic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
			$logic->create($page);
			
			if(isset($_POST["object"])){
				$object = $page->getPageObject();
				SOY2::cast($object,(object)$_POST["object"]);
				$object->save();
			}
		}
		
		//テンプレートから作成する場合
		if(isset($_POST["directory_template"]) && empty($_POST["directory_template"])){
			
			$templateId = str_replace(array(".","-"),"_",$page->getUri());
			$templateId = SOY2Logic::createInstance("site.logic.page.template.TemplateLogic")
				->createTemplate($templateId,$page->getName() . "のテンプレート", $_POST["new_dir_template"], "detail");
			
			$page->setTemplate($templateId);
			$page->save();
			
			
			//indexのテンプレートを指定している場合
			if(isset($_POST["page_template"]) && empty($_POST["page_template"])){
				$index = SOY2DAO::find("SOYCMS_Page",array("uri" => $page->getIndexUri()));
				$templateId = str_replace(array(".","-"),"_",$index->getUri());
				$templateId = SOY2Logic::createInstance("site.logic.page.template.TemplateLogic")
					->createTemplate($templateId,$index->getName() . "のテンプレート", $_POST["new_template"], $_POST["template_type"]);
				
				$index->setType($_POST["template_type"]);
				$index->setTemplate($templateId);
				$index->save();
				
			}
		
		//ページの作成でテンプレートを指定する場合
		}else if(isset($_POST["page_template"]) && empty($_POST["page_template"])){
			$templateId = str_replace(array(".","-"),"_",$page->getUri());
			
			$templateId = SOY2Logic::createInstance("site.logic.page.template.TemplateLogic")
				->createTemplate($templateId,$page->getName() . "のテンプレート", $_POST["new_template"], $_POST["template_type"]);
			
			$page->setType($_POST["template_type"]);
			$page->setTemplate($templateId);
			$page->save();
		}
		
		$id = $page->getId();
		
		SOY2PageController::jump("page.list?created=$id");
	}

	function page_page_create(){
		WebPage::WebPage();
		
		$newPage = null;
		
		$this->createAdd("dir_form","_class.form.PageForm");
		$this->createAdd("form","_class.form.PageForm");
		
		$this->addModel("type_dir",array(
			"visible" => (!isset($_GET["type"]) || $_GET["type"] == "dir")
		));
		$this->addModel("type_page",array(
			"visible" => (isset($_GET["type"]) && $_GET["type"] != "dir")
		));
		
		$this->createAdd("directory_tree","_class.list.PageTreeComponent",array(
			"type" => "detail",
			"checkboxName" => "object[directory]",
			"selected" => array(1) 
		));
		
		//親が指定
		$parent = null;$index = null;
		if(isset($_GET["parent"])){
			$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
			try{
				$parent = $pageDAO->getById($_GET["parent"]);
				$index = $pageDAO->getByUri($parent->getIndexUri());
			}catch(Exception $e){
				
			}
			
		//指定されない時はhome
		}else{
			$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
			try{
				$parent = $pageDAO->getByUri("_home");
				$index = $pageDAO->getByUri($parent->getIndexUri());
			}catch(Exception $e){
				
			}
		}
		
		/*
		 * テンプレートの情報 
		 */
		$list = SOYCMS_Template::getList();
		$dirList = array();
		$indexList = array();
		
		foreach($list as $key => $template){
			if($template->getType() == "detail"){
				$dirList[$key] = $template;
			}else{
				if(!isset($_GET["page_type"])){
					if(in_array($template->getType(),array("list","default","search"))){
						$indexList[$key] = $template;
					}
				}else{
					if($template->getType() == $_GET["page_type"]){
						$indexList[$key] = $template;
						if(!$index){
							$index = new SOYCMS_Page();
						}
						
						$index->setTemplate($key);
					}
				}
			}
		}
		
		$this->addInput("page_type_hidden",array(
			"name" => "Page[type]",
			"value" => @$_GET["page_type"],
			"visible" => (isset($_GET["page_type"]))
		));

		$this->createAdd("directory_template_list","_class.list.TemplateList",array(
			"inputName" => "directory_template",
			"list" => $dirList,
			"selected" => ($parent) ? $parent->getTemplate() : null
		));
		
		$this->createAdd("template_list","_class.list.TemplateList",array(
			"inputName" => "page_template",
			"list" => $indexList,
			"selected" => ($index) ? $index->getTemplate() : null
		));
		
		$this->addModel("tempplate_list_wrap",array(
			"visible" => $indexList
		));
		
		$this->addSelect("template_type_select",array(
			"options" => SOYCMS_Template::getTypes(),
			"selected" => "list",
			"property" => "name",
			"name" => "template_type",
		));
		
		//基本のテンプレート
		$this->addTextArea("new_dir_template",array(
			"name" => "new_dir_template",
			"value" => file_get_contents(dirname(__FILE__) . "/_create/dir.html")
		));
		$this->addTextArea("new_template",array(
			"name" => "new_template",
			"value" => file_get_contents(dirname(__FILE__) . "/_create/index.html")
		));
		
		//テンプレートを作成可能か
		$this->addModel("template_creatable",array(
			"visible" => (@$_GET["page_type"] != "app")
		));
		
	}
}