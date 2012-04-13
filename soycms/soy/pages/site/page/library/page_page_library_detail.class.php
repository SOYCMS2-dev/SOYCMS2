<?php
/**
 * @title ライブラリの作成
 */
class page_page_library_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Library"])){
			SOY2::cast($this->library,(object)$_POST["Library"]);
			$this->library->save();
			
			SOYCMS_History::addHistory("library",array($this->library->getHistoryKey(),$this->library));
		}
		
		if(isset($_POST["library_ini"]) && isset($_GET["ini"]) && soy2_check_token()){
			$path = SOYCMS_Library::getLibraryDirectory() . $this->library->getId() . "/library.ini";
			$content = $_POST["library_ini"];
			if(parse_ini_string($content)){
				file_put_contents($path, $content);
			}
		}
		
		if(isset($_GET["page"]) && is_numeric($_GET["page"])){
			$this->jump("/page/detail/" . (int)$_GET["page"] . "#tpl_config");
		}
		
		if(isset($_GET["template"])){
			$this->jump("/page/library/detail?id=" . $this->id . "&template=".$this->library->getTemplateType()."&updated");
		}else{
			$this->jump("/page/library/detail?id=" . $this->id . "&updated");
		}
	}
	
	private $id;
	private $library;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->library = SOYCMS_Library::load($this->id);
		if(!$this->library){
			$this->jump("/page/library");
		}
		
		if(isset($_GET["template"])){
			$this->library->setTemplateType($_GET["template"]);
			$this->library->loadTemplate();
		}
		
		parent::prepare();
	}

	function page_page_library_detail(){
		WebPage::WebPage();
		
		$this->addModel("template_type_normal",array(
			"visible" => count($this->library->getTemplates()) < 1
		));
		
		$this->addModel("template_type_complex",array(
			"visible" => count($this->library->getTemplates()) > 0
		));
		
		$this->addModel("mode_ini",array(
			"visible" => (isset($_GET["ini"]))
		));
		
		$this->createAdd("template_complex_type_list","_class.list.TemplateComplexTypeList",array(
			"list" => $this->library->getTemplates(),
			"link" => soycms_create_link("page/library/detail?id=" . $this->library->getId())
		));
		
		$this->addTextArea("ini_content",array(
			"name" => "library_ini",
			"value" => (isset($_GET["ini"])) ? file_get_contents(SOYCMS_Library::getLibraryDirectory() . $this->library->getId() . "/library.ini") : ""
		));
		
		$this->createAdd("form","_class.form.LibraryForm",array(
			"library" => $this->library
		));
		
		$this->addLink("copy_link",array(
			"link" => soycms_create_link("page/library/create?id=") . $this->id
		));
		
		$this->createAdd("history_index","page.history.page_page_history_index",array(
			"type" => "library",
			"name" => $this->library->getName(),
			"objectId" => $this->library->getHistoryKey()
		));
		
		
	}
}