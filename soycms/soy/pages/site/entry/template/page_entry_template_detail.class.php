<?php
/**
 * 記事雛形の作成
 */
class page_entry_template_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->directory->getId(),true)){
			$this->goError();
			exit;
		}
	
		if(isset($_POST["save"])){
			$template = $this->template;
			
			//EntryLogic
			$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntryLogic");
			
			//section
			$logic->saveSections($template,$_POST["section"]);
			
			//上書き
			SOY2::cast($template,$_POST["EntryTemplate"]);
			
			//保存
			$template->save();
			
			$this->jump("/entry/template/detail/" . $template->getId() . "?created");
		}
		
		if(isset($_POST["entry"])){
			$template = $this->template;
			$this->jump("/entry/create/" . $template->getDirectory() . "/" . $template->getId());
		}
		
		if(isset($_POST["remove"])){
			
			$this->template->delete();
			$this->jump("/entry/template/list?removed");
		}
	}
	
	
	private $id;
	private $template;
	private $directory;
	
	function init(){
		try{
			$this->template = SOY2DAO::find("SOYCMS_EntryTemplate",$this->id);
			$this->directory = SOY2DAO::find("SOYCMS_Page",$this->template->getDirectory());
		}catch(Exception $e){
			$this->jump("entry/template/");
		}
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->directory->getId())){
			$this->goError();
			exit;
		}
	}
	

	function page_entry_template_detail($args) {
		$this->id = @$args[0];
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->buildPage();
		$this->buildScript();
	}
	
	function buildPage(){
		$template = $this->template;
		
		/* dir */
		$this->addLabel("directory_name",array(
			"text" => $this->directory->getName()
		));
		
		$this->addLink("directory_template_link",array(
			"link" => soycms_create_link("entry/template/list/" . $this->directory->getId())
		));
		
		
		/* template */
		
		$this->addInput("template_name",array(
			"name" => "EntryTemplate[name]",
			"value" => $template->getName()
		));
		
		
		$this->addInput("template_title",array(
			"name" => "EntryTemplate[title]",
			"value" => $template->getTitle()
		));
		
		$this->addTextArea("template_description",array(
			"name" => "EntryTemplate[description]",
			"value" => $template->getDescription()
		));
		
		
		/* sections */
		
		$sections = SOYCMS_EntrySection::unserializeSection($template->getSections());
		if(empty($sections)){
			$section = new SOYCMS_EntrySection();
			$section->setType("wysiwyg");
			$section->setValue("");
			$section->setContent("");
			$sections = array(
				$section
			);
		}
		
		$this->createAdd("section_list","entry.page_entry_editor",array(
			"arguments" => array($sections)
		));
		
		/* editor */
		$dir = $this->directory;
		
		//ディレクトリ毎に要素の設定を読み込む
		$config = $dir->getConfigObject();
		$orders = (isset($config["append_snippet_order"])) ? $config["append_snippet_order"] : array();
		$allow = (isset($config["allowed_append_snippet"])) ? $config["allowed_append_snippet"] : array();
		$ordersInsert = (isset($config["insert_snippet_order"])) ? $config["insert_snippet_order"] : array();
		$allowInsert = (isset($config["allowed_insert_snippet"])) ? $config["allowed_insert_snippet"] : array();
		
		SOY2::import("site.logic.entry.SOYCMS_EditorManager");
		$this->addLabel("append_new_sections",array(
			"html" => SOYCMS_EditorManager::bulidSectionMenus($orders,$allow)
		));
		$this->addLabel("insert_new_sections",array(
			"html" => SOYCMS_EditorManager::buildInsertSectionMenus($ordersInsert,$allowInsert)
		));
		
		$this->addModel("add_child_entry_snippet",array(
			"visible" => (in_array("nextpage",$allow) || !in_array("nextpage",$ordersInsert))
		));
	}
	
	
	/**
	 * JavaScript関連を作成
	 */
	function buildScript(){
		
		$scripts = array();
		$scripts[] = "var EDITOR_ACTION_URL = \"".SOY2FancyURIController::createLink("/entry/editor")."?template_id=".$this->id."\";";
		$scripts[] = "var EDITOR_HISTORY_URL = \"".SOY2FancyURIController::createLink("/entry/history/list")."?template_id=".$this->id."\";";
		
		$this->createAdd("ajax_action","HTMLScript",array(
			"script" => implode("\n",$scripts)
		));
	}
}
?>