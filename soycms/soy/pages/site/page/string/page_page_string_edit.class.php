<?php
class page_page_string_edit extends SOYCMS_WebPageBase{
	
	private $config;
	private $lang = array();
	
	function doPost(){
		
		if(isset($_POST["String"])){
			
			foreach($_POST["String"] as $lang => $array){
				$this->config->save($lang, $array);
				
				SOYCMS_History::addHistory("string",array(
					$lang,
					$this->config->export("csv", $array),
					$this->config->getName() . " - " . $lang
				));
				
			}
			$this->jump("page/string/?updated");
		}
	}
	
	function init(){
		try{
			$this->config  = SOY2String::load(SOYCMS_SITE_DIRECTORY . ".i18n/site.json");
		}catch(Exception $e){
			$this->jump("/");
		}
		
		if(isset($_GET["lang"]) && is_array($_GET["lang"])){
			$this->lang = $_GET["lang"];
		}
	}
	
	function page_page_string_edit(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$lang1 = (isset($this->lang[0])) ? $this->lang[0] : $this->config->getDefault();
		$lang2 = (isset($this->lang[1])) ? $this->lang[1] : "";
		if(!$lang2 && $lang1 != $this->config->getDefault())$lang2 = $this->config->getDefault();
		
		$this->addLabel("lang1",array(
			"text" => $lang1
		));
		$this->addLabel("lang2",array(
			"text" => ($lang2) ? $lang2 : ""
		));
		
		$langs = array_keys($this->config->getLanguage());
		$langs = array_combine($langs, $langs);
		$this->addSelect("language_select1",array(
			"name" => "lang[]",
			"options" => $langs,
			"selected" => $lang1
		));
		$this->addSelect("language_select2",array(
			"name" => "lang[]",
			"options" => $langs,
			"selected" => $lang2
		));
		$this->addModel("lang_select_wrap",array(
			"style" => ($lang2) ? "display:none;" : "display:block;"
		));
		
		$this->createAdd("string_list","_StringList",array(
			"list" => $this->config->load($this->config->getDefault()),
			"lang1" => $lang1,
			"lang2" => $lang2,
			"mainlang" => $this->config->load($lang1),
			"sublang" => ($lang2) ? $this->config->load($lang2) : array()
		));
	
	}

}

class _StringList extends HTMLList{
	
	private $mainlang = null;
	private $sublang = null;
	private $lang1 = null;
	private $lang2 = null;
	
	
	function populateItem($default,$key){
		$lang1 = $this->lang1;
		$lang2 = $this->lang2;
		
		$blank = ($default) ? $default : "[空白]";
		
		$this->addLabel("string_key",array(
			"text" => $key
		));
		
		
		$entity = ($this->mainlang && isset($this->mainlang[$key])) ? $this->mainlang[$key] : "";
		
		$this->addLabel("string_text",array(
			"html" => ($entity) ? nl2br(htmlspecialchars($entity)) : $blank
		));
		
		$this->addTextArea("string_edit",array(
			"name" => "String[" . $lang1 . "][" . $key ."]",
			"value" => $entity,
			"visible" => $lang1
		));
		
		$entity2 = ($this->sublang && isset($this->sublang[$key])) ? $this->sublang[$key] : $default;
		
		$this->addLabel("string2_text",array(
			"html" => ($entity2) ? nl2br(htmlspecialchars($entity2)) : $blank,
			"visible" => $this->sublang
		));
		
		$this->addTextArea("string2_edit",array(
			"name" => "String[" . $lang2 . "][" . $key ."]",
			"value" => $entity2,
			"visible" => $lang2
		));
	}
	
	function setSublang($list){
		$this->sublang = $list;
	}
	function setMainlang($list){
		$this->mainlang = $list;
	}
	
	

	public function getLang1(){
		return $this->lang1;
	}

	public function setLang1($lang1){
		$this->lang1 = $lang1;
		return $this;
	}

	public function getLang2(){
		return $this->lang2;
	}

	public function setLang2($lang2){
		$this->lang2 = $lang2;
		return $this;
	}
}