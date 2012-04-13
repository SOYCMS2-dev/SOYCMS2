<?php
class SOYCMS_DetailPage extends SOYCMS_PageBase{
	
	private $defaultTitle = "新しい記事";
	
	private $urlType = 0;
	private $urlTypeOption = "";
	
	private $isOutputFeed = true;
	private $feedEntryCount = 10;
	
	private $feedRDF = array(
		"output" => true,
		"uri" => "rss.xml",
		"output_type" => "excerpt",
		"excerpt_size" => 100,
		"title" => "#SiteName# - #DirName# - RSS1.0"
	);
	private $feedRSS = array(
		"output" => true,
		"uri" => "rss2.xml",
		"output_type" => "excerpt",
		"excerpt_size" => 100,
		"title" => "#SiteName# - #DirName# - RSS2.0"
	);
	private $feedAtom = array(
		"output" => true,
		"uri" => "atom.xml",
		"output_type" => "excerpt",
		"excerpt_size" => 100,
		"title" => "#SiteName# - #DirName# - Atom"
	);
	
	private $entryPosition = -1;
	
	private $indexUri = "index.html";
	
	/**
	 * 新規投入時に呼ばれる
	 * 管理画面からユニークなURIを作成するルールを定める
	 */
	function getEntryUri(SOYCMS_Entry $entry){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryDAO");
		
		$urlType = $this->getUrlType();
		$createDate = (is_string($entry->getCreateDate())) ? strtotime($entry->getCreateDate()) : $entry->getCreateDate();
		
		$prefix = preg_replace('/\/[^\/]+$/',"",$entry->getUri());
		
		switch($urlType){
			case 0:
				$uri = "entry.html";
				break;
			case 1:
				$uri = rawurlencode($entry->getTitle()) . ".html";
				break;
			case 2:
				$uri = rawurlencode($entry->getTitle());
				break;
			case 3:
				$uri = date("Ymd", $createDate) . ".html";
				break;
			case 4:
				$uri = date("Ymd", $createDate) . "_" . rawurlencode($entry->getTitle()) . ".html";
				break;
			case 5:
				$uri = $entry->getId() . "";
				break;
			case 6:
				$uri = $this->getUrlTypeOption();
				//・作成年 %YYYY%
				//・作成月 %MM%
				//・作成日 %DD%
				//・記事タイトル %TITLE%
				//・記事ID　%ID%
				$uri = str_replace("%YYYY%",date("Y",$entry->getCreateDate()),$uri);
				$uri = str_replace("%MM%",date("m",$entry->getCreateDate()),$uri);
				$uri = str_replace("%DD%",date("d",$entry->getCreateDate()),$uri);
				$uri = str_replace("%TITLE%",rawurlencode($entry->getTitle()),$uri);
				$uri = str_replace("%ID%",$entry->getId(),$uri);
				break;
		}
		
		
		if(strlen($prefix) > 0){
			$uri = $prefix . "/" . $uri;
		}
		
		//URI_2.html,URI_3.htmlのようにチェックをしていく。
		$uri_array = pathinfo($uri);
		if($uri_array["dirname"] == ".")$uri_array["dirname"] = "";
		if(strlen($uri_array["dirname"])>0)$uri_array["dirname"] .= "/";
		if(strlen(@$uri_array["extension"])>0)$uri_array["extension"] = "." . $uri_array["extension"];
		$counter = $dao->countByUri(
			$uri_array["dirname"]  . $uri_array["filename"] . "_%",
			$this->getPage()->getId()
		) + 1;
		
		//数が余りにも多い場合は非常に非効率ため、検討が必要
		while($dao->checkUri($uri,$this->getPage()->getId(),$entry->getId())){
			$uri = $uri_array["dirname"]  . $uri_array["filename"] . "_" . $counter . @$uri_array["extension"];
			$counter++;
		}
		
		return $uri;
	}
	
	/**
	 * 新規投入時に呼ばれる
	 * 記事の件名（新規作成時）を取得
	 */
	function getEntryTitle(){
		$title = $this->getDefaultTitle();
		$title = str_replace("%YYYY%",date("Y"),$title);
		$title = str_replace("%MM%",date("m"),$title);
		$title = str_replace("%DD%",date("d"),$title);
		
		if(strpos($title,'%VOL%') !== false){
			$dao = SOY2DAOContainer::get("SOYCMS_EntryDAO");
			$vol = $dao->countByDirectory($this->getPage()->getId());
			$title = str_replace('%VOL%',$vol,$title);
		}
		
		return $title;
	}
	
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/DetailPageFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("DetailPageFormPage",array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 標準で追加されるブロックを表示
	 */
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事詳細を表示",
			"pager" => "ページャー",
			"comment_form" => "コメントフォーム",
			"comment_list" => "コメント一覧",
			"trackback_list" => "トラックバック一覧",
			"child_entry_list" => "子記事一覧",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
	/**
	 * フィード用のページを生成
	 */
	function generateFeedPage(){
		
		$dao = SOY2DAOContainer::get("SOYCMS_PageDAO");
		$pageLogic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
		
		$pageId = $this->getPage()->getId();
		$pageUri = $this->getPage()->getUri();
		
		$pages = $dao->getByParent($pageId);
		$pages = array_values($pages);
		
		//フィードの設定
		$feeds = array(
			$this->getFeedRDF(),
			$this->getFeedRSS(),
			$this->getFeedAtom(),
		);
		
		$name = $this->getPage()->getName();
		$names = array(
			$name . "- RSS1.0",
			$name . "- RSS2.0",
			$name . "- Atom",
		);
		
		//テンプレート
		$templates = array(
			dirname(__FILE__) . "/". get_class($this) ."/template/rdf.html",
			dirname(__FILE__) . "/". get_class($this) ."/template/rss.html",
			dirname(__FILE__) . "/". get_class($this) ."/template/atom.html",
		);
		
		//変更後のURLが大丈夫かどうかチェック
		$mapping = SOYCMS_DataSets::load("site.url_mapping");
		$uris = array();
		for($i=0;$i<3;$i++){
			if(!isset($pages[$i]))$pages[$i] = new SOYCMS_Page();
			$page = $pages[$i];
			$feed = $feeds[$i];
			
			if(strlen($feed["uri"])<1){
				throw new Exception("");
			}
			
			$uri = soycms_union_uri($pageUri,$feed["uri"]);
			
			if(isset($mapping[$uri]) && $page->getId() && $mapping[$uri] != $page->getId()){
				throw new Exception("");
			} 
		}
		
		
		
		for($i=0;$i<3;$i++){
			if(!isset($pages[$i]))$pages[$i] = new SOYCMS_Page();
			$page = $pages[$i];
			$feed = $feeds[$i];
			
			$page->setName($names[$i]);
			$page->setType(".feed");
			$page->setParent($pageId);
			$uri = soycms_union_uri($pageUri,$feed["uri"]);
			if($uri[strlen($uri)-1] == "/")$uri = substr($uri,0,strlen($uri)-1);
			$page->setUri($uri);
			
			//config
			$config = $page->getConfigObject();
						
			//公開するかどうか
			$config["public"] = $feed["output"] * $this->getIsOutputFeed();
			$config["content-type"] = "application/xml";
			$config["title"] = @$feed["title"];
			
			//configの保存
			$page->setConfigObject($config);
			$pageLogic->generatePageClass($page);
			$pageLogic->updatePageObject($page);
			
			//objectの保存
			$object = $page->getPageObject();
			$object->setDirectory($pageId);
			$object->setLimit($this->getFeedEntryCount());
			$object->setExcerpt($feed["excerpt_size"]);
			$object->setType($feed["output_type"]);
			$object->save();
			
			//テンプレート
			file_put_contents(
				$page->getPageDirectory() . "template.html",
				file_get_contents($templates[$i])
			);
			
			$page->save();
		}
	}
	
	/**
	 * 保存
	 */
	function save(){
		//RSS周りを上書き
		$this->generateFeedPage();
		parent::save();
	}
	
	/* getter setter */
   

	function getUrlType() {
		return $this->urlType;
	}
	function setUrlType($urlType) {
		$this->urlType = $urlType;
	}
	function getUrlTypeOption() {
		return $this->urlTypeOption;
	}
	function setUrlTypeOption($urlTypeOption) {
		$this->urlTypeOption = $urlTypeOption;
	}

	function getIsOutputFeed() {
		return $this->isOutputFeed;
	}
	function setIsOutputFeed($isOutputFeed) {
		$this->isOutputFeed = $isOutputFeed;
	}
	function getFeedRDF() {
		return $this->feedRDF;
	}
	function setFeedRDF($feedRDF) {
		$this->feedRDF = $feedRDF;
	}
	function getFeedRSS() {
		return $this->feedRSS;
	}
	function setFeedRSS($feedRSS) {
		$this->feedRSS = $feedRSS;
	}
	function getFeedAtom() {
		return $this->feedAtom;
	}
	function setFeedAtom($feedAtom) {
		$this->feedAtom = $feedAtom;
	}

	function getFeedEntryCount() {
		return $this->feedEntryCount;
	}
	function setFeedEntryCount($feedEntryCount) {
		$this->feedEntryCount = $feedEntryCount;
	}

	function getEntryPosition() {
		return $this->entryPosition;
	}
	function setEntryPosition($entryPosition) {
		$this->entryPosition = $entryPosition;
	}

	function getDefaultTitle() {
		return $this->defaultTitle;
	}
	function setDefaultTitle($defaultTitle) {
		$this->defaultTitle = $defaultTitle;
	}
	
		function getIndexUri() {
		return $this->indexUri;
	}
	function setIndexUri($indexUri) {
		$this->indexUri = $indexUri;
	}
}
?>