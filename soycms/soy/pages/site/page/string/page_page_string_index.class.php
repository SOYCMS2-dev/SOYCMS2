<?php
class page_page_string_index extends SOYCMS_WebPageBase{
	
	private $config;
	
	function init(){
		try{
			$this->config  = SOY2String::load(SOYCMS_SITE_DIRECTORY . ".i18n/site.json");
		}catch(Exception $e){
			$this->jump("/");
		}
	}
	
	function page_page_string_index(){
		WebPage::WebPage();
		
		//言語ファイルを取得
		$config = $this->config;
		
		$this->addLabel("base_language",array(
			"text" => __($config->getDefault())
		));
		$this->createAdd("language_list","_LanguageList",array(
			"list" => $config->getLanguageFileList()
		));
		
	}
}

class _LanguageList extends HTMLList{
	
	/**
	 * @var SOYCMS_HistoryDAO
	 */
	private $historyDAO = null;
	
	function populateItem($entity,$key){
		if(!$this->historyDAO){
			$this->historyDAO = SOY2DAOContainer::get("SOYCMS_HistoryDAO");
		}
		
		$this->addLabel("language_text",array(
			"text" => __($key)
		));
		$this->addLabel("language_update_date",array(
			"text" => soy2_date("Y-m-d H:i:s", filemtime($entity))
		));
		
		$this->addLink("edit_link",array(
			"link" => soycms_create_link("page/string/edit") . "?lang[]=" . $key
		));
		
		$count = 0;
		try{
			$count = $this->historyDAO->countByParams("string", $key);
		}catch(Exception $e){
			
		}
		$this->addLabel("history_count",array(
			"text" => $count
		));
		
		$this->addLink("show_history_link",array(
			"link" => soycms_create_link("page/string/detail") . "?lang=" . $key
		));
	}
	
}