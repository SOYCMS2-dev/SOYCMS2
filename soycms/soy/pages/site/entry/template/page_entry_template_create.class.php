<?php
/**
 * 記事雛形の作成
 */
class page_entry_template_create extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->directory->getUri(),true)){
			$this->goError();
			exit;
		}

		if(isset($_POST["create"])){
			
			$template = new SOYCMS_EntryTemplate();
			SOY2::cast($template,$this->entry);
			SOY2::cast($template,$_POST["EntryTemplate"]);
			
			//IDもコピーされるのでnullに
			$template->setId(null);
			
			//作成
			$template->save();
			
			if($template->getId()){
				$this->jump("/entry/template/detail/" . $template->getId() . "?created");
			}
		}
		
		
	}
	
	
	private $id;
	private $entry;
	private $directory;
	
	function init(){
		try{
			$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
			$this->directory = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			$this->jump("entry/template/");
		}
		
		//permission check
		//作成だけど書き込み権限チェック
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->directory->getUri(),true)){
			$this->goError();
			exit;
		}
	}
	

	function page_entry_template_create($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->buildPage();
		$this->buildScript();
	}
	
	function buildPage(){
		$entry = $this->entry;
		
		$this->addLink("return_link",array(
			"link" => soycms_create_link("/entry/detail/" . $entry->getId())
		));
		
		$this->addInput("template_name",array(
			"name" => "EntryTemplate[name]",
			"value" => $this->directory->getName() ." | " . $entry->getTitle() . "の記事テンプレート"
		));
		
		$this->addInput("template_title",array(
			"name" => "EntryTemplate[title]",
			"value" => $entry->getTitle()
		));
		
		$this->addTextArea("template_description",array(
			"name" => "EntryTemplate[description]",
			"value" => $entry->getTitle() . "から作成した記事テンプレートです。"
		));
		
		
		/* sections */
		
		$sections = SOYCMS_EntrySection::unserializeSection($entry->getSections());
		if(empty($sections)){
			$section = new SOYCMS_EntrySection();
			$section->setType("wysiwyg");
			$section->setValue("");
			$section->setContent("");
			$sections = array(
				$section
			);
		}
		
		foreach($sections as $key => $section){
			$sections[$key]->setType("preview");
		}
		
		$this->createAdd("section_list","entry.page_entry_editor",array(
			"arguments" => array($sections)
		));
	}
	
	
	/**
	 * JavaScript関連を作成
	 */
	function buildScript(){
		
		$scripts = array();
		$scripts[] = "var EDITOR_ACTION_URL = \"".SOY2FancyURIController::createLink("/entry/editor")."?entryId=".$this->id."\";";
		$scripts[] = "var EDITOR_HISTORY_URL = \"".SOY2FancyURIController::createLink("/entry/history/list")."?entryId=".$this->id."\";";
		
		$this->createAdd("ajax_action","HTMLScript",array(
			"script" => implode("\n",$scripts)
		));
	}
}
?>