<?php

class SOYCMS_EntryLogic extends SOY2LogicBase{

	/**
	 * @param $entry 保存する記事
	 * @param $force 
	 * @param $syncHistory = true ヒストリーに追加するか
	 */
	function update(SOYCMS_Entry $entry, $force = false, $syncHistory = true){
		
		//セクション周りの管理
		$content = "";
		
		if(isset($_POST["section"]) && is_array($_POST["section"])){
			$content = $this->saveSections($entry,$_POST["section"]);
		}
		
		/*
		 * 公開中は保存しない
		 */
		if($entry->getPublish() < 1 || $force){
			$entry->setTitle($entry->getTitleSection());
			$entry->setContent($content);
			
			//最後に同期を取った時刻を保存
			$entry->setLastUpdateDate(time());
		}
		
		//保存
		if(strlen($entry->getUri())<1){
			
			//page
			$page = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory());
			
			try{
				$obj = $page->getPageObject();
				$uri = (method_exists($obj,"getEntryUri")) ? $obj->getEntryUri($entry) : "";
			}catch(Exception $e){
				$uri = "entry_" . $entry->getId() . ".html";
			}
			
			$entry->setUri($uri);
			$dirUri = $page->getUri();
			$entry->setDirectoryUri($dirUri);
			
		}else{
			$entry->setUri($entry->getUri());
			
			$page = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory());
			$dirUri = $page->getUri();
			$entry->setDirectoryUri($dirUri);
		}
		
		
		
		//保存実行
		if($syncHistory)$this->onUpdate($entry);
		$entry->save();
		
		//子記事を変更
		if(!$entry->getParent()){
			$childEntries = SOY2DAO::find("SOYCMS_Entry",array("parent"=> $entry->getId()));
			foreach($childEntries as $childEntry){
				$childEntry->setDirectory($entry->getDirectory());
				$childEntry->save();
			}
		}
		
	}
	
	/**
	 * 記事が更新された時に呼び出す
	 */
	function onUpdate($entry){
	
		//addHistory
		SOYCMS_EntryHistory::addHistory($entry,"update",$entry->getMemo());
		$count = SOYCMS_DataSets::get("entry_history_count",20);
		SOYCMS_EntryHistory::clearHistory($entry->getId(),$count);
	
		try{
			$directory = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory());
		//@TODO ディレクトリがおかしい時はなんかしたい
		}catch(Exception $e){
			return;
		}
		
		//Send Update Ping
		$url = soycms_get_page_url($directory->getUri(),$entry->getUri());
		$title = $entry->getTitle();
		$this->sendUpdatePing($entry,$title,$url);
		
		//ディレクトリの時は同期しない
		if($directory->isDirectory())return;
		
		//公開設定を同期する
		$config = $directory->getConfigObject();
		$config["public"] = ($entry->getPublish()) ? 1 : 0;
		$directory->setConfigObject($config);
		$directory->save();
		
		$entry->setOrder(-1);
	}
	
	/**
	 * Sectionの保存
	 * @param SOYCMS_Entry
	 * @param array sections
	 */
	function saveSections($entry,$sescions){
		
		$moresectionflag = false;	//moreセクションを複数追加させないため
		$content = "";
		
		foreach($sescions as $name => $section){
			if(!isset($section["type"]))continue;
			if(!isset($section["content"]))continue;
			if(!isset($section["value"]))continue;
			if(@$section["remove"] == 1)continue;
			
			$type = $section["type"];
			$obj = SOYCMS_EntrySection::getSection($name,$type);
			$obj->setContent($section["content"]);
			$obj->setValue($section["value"]);
			$obj->setSnippet($section["snippet"]);
			
			$sections[] = $obj;
		}
		
		if(!@$sections)$sections = array();
		$entry->setSections($sections);
		$content = $entry->buildContent();
		
		return $content;
	}
	
	/**
	 * 記事のコピー
	 */
	function copy($entry,$dirId){
		$entry->setId(null);
		$entry->setDirectory($dirId);
		$entry->setPublish(0);
		$entry->setStatus("draft");
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$page = SOY2DAO::find("SOYCMS_Page",$dirId);
		if(!$page->isDirectory())throw new Exception();
		$pageObj = $page->getPageObject();
		
		$entry->setCreateDate(time());
		$entry->setUri(null);
		$uri = $pageObj->getEntryUri($entry);
		$entry->setUri($uri);
		
		
		if($pageObj->getEntryPosition() > 0){
			$order = $dao->countByDirectory($dirId);
			$entry->setOrder($order);
		}else{
			$entry->setOrder(0);
			$dao->updateOrders();
		}
		
		$entry->save();
		
		return $entry->getId();
	}
	
	/**
	 * 更新Pingの送信
	 */
	function sendUpdatePing($entry,$title,$url){
		if(!$entry->getAttribute("send_ping"))return;
		
		$data =
			   "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" .
			   "<methodCall>" .
			   "<methodName>weblogUpdates.ping</methodName>" .
			   "<params>" .
			   "<param>" .
			   "<value>$title</value>" .
			   "</param>" .
			   "<param>" .
			   "<value>$url</value>" .
			   "</param>" .
			   "</params>" .
			   "</methodCall>";
		
		//header
		$header = array(
			"Content-Type: application/x-www-form-urlencoded",
			"Content-Length: ".strlen($data)
		);
		
		$context = array(
			"http" => array(
				"method"  => "POST",
				"header"  => implode("\r\n", $header),
				"content" => $data
			)
		);
		
		$destination = SOYCMS_DataSets::get("update_ping_target","");
		
		//送信実行
		//エラー抑制のために@を追加
		$list = explode("\n",$destination);
		foreach($list as $url){
			$url = trim($url);
			if(!preg_match('/^https?:\/\//',$url))continue;
			$xml = @file_get_contents($url, false, stream_context_create($context));
		}
	}
}
?>