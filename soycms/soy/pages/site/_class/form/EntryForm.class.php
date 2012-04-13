<?php

class EntryForm extends HTMLForm{
	
	private $entry;
	
	function init(){
		$entry = $this->getEntry();
	}
	
	
	function execute(){
		$entry = $this->getEntry();
		
		$this->createAdd("entry_title","HTMLInput",array(
			"name" => "Entry[titleSection]",
			"value" => $entry->getTitleSection(),
		));
		
		$this->createAdd("entry_uri","HTMLInput",array(
			"name" => "Entry[uri]",
			"value" => $entry->getUri(),
		));
		
		$sections = $entry->getSectionsList();
		if(empty($sections)){
			$section = new SOYCMS_EntrySection();
			$section->setType("wysiwyg");
			$section->setValue($entry->getContent());
			$section->setContent($entry->getContent());
			$sections = array(
				$section
			);
		}
		
		$this->addLabel("section_list",array(
			"html" => SOYCMS_EditorManager::buildSections($sections)
		));
		
		$this->createAdd("create_date","_class.component.HTMLDateInput",array(
			"name" => "Entry[createDate]",
			"value" => $entry->getCreateDate(),
		));
		
		$dirs = $this->getDirectories();
		$this->createAdd("dir_select","HTMLSelect",array(
			"name" => "Entry[directory]",
			"value" => $entry->getDirectory(),
			"options" => $dirs,
			"property" => "name"
		));
		
		$this->createAdd("entry_link","HTMLLink",array(
			"link" => (isset($dirs[$entry->getDirectory()])) ? soycms_union_uri(SOYCMS_SITE_ROOT_URL,soycms_union_uri($dirs[$entry->getDirectory()]->getUri(),rawurldecode($entry->getUri()))) : "",
			"visible" => (isset($dirs[$entry->getDirectory()]))
		));
		
		parent::execute();
	}

	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
	
	/**
	 * 記事ディレクトリ一覧取得
	 */
	function getDirectories(){
		$array = SOY2DAO::find("SOYCMS_Page",array("type" => "detail"));
		return $array;
	}
}


?>