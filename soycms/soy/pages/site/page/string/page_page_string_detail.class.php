<?php
class page_page_string_detail extends SOYCMS_WebPageBase{
	
	private $id = null;
	private $history = null;
	private $lang = "ja";
	private $config;
	
	/**
	 * @var SOYCMS_HistoryDAO
	 */
	private $historyDAO = null;
	
	function init(){
		try{
			$this->config  = SOY2String::load(SOYCMS_SITE_DIRECTORY . ".i18n/site.json");
		}catch(Exception $e){
			
		}
		
		$this->historyDAO = SOY2DAOContainer::get("SOYCMS_HistoryDAO");
		
		if(isset($_GET["lang"]))$this->lang = $_GET["lang"];
		if(isset($_GET["id"]))$this->lang = $_GET["id"];
		
		if(isset($_GET["rollback"])){
			try{
				$this->history = $this->historyDAO->getById($_GET["rollback"]);
				if($this->history->getObjectId() != $this->lang || $this->history->getObject() != "string"){
					throw new Exception();
				}
			}catch(Exception $e){
				$this->jump("/page/string/detail?failed&lang=" . $this->lang);
			}
			$content = $this->history->getContent();
			SOYCMS_History::addHistory("string",array(
				$this->lang,
				$content,
				$this->config->getName() . " - " . $this->lang
			),"revert");
			
			$list = $this->config->getLanguageFileList();
			if(isset($list[$this->lang])){
				file_put_contents($list[$this->lang],$content);
			}
		}
	}
	
	function page_page_string_detail(){
		WebPage::WebPage();
		
		//言語ファイルを取得
		$config = $this->config;
		
		$this->addLabel("selected_language",array(
			"text" => $this->lang
		));
		
		$this->historyDAO->setLimit(20);
		$histories = $this->historyDAO->listByParams("string",$this->lang);
		$this->createAdd("history_list","_class.list.HistoryList",array(
			"list" => $histories
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
			"link" => soycms_create_link("page/string/history") . "?lang=" . $key
		));
	}
	
}