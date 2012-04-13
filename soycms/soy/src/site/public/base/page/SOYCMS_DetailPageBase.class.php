<?php

class SOYCMS_DetailPageBase extends SOYCMS_SitePageBase{
	
	protected $entry;
	private $comment;
	private $dao;
	
	function SOYCMS_DetailPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}
	
	function checkEntry($entry){
		if($entry->getDirectory() != $this->getPageObject()->getId()){
			return false;
		}
		
		$this->entry = $entry;
		
		return true;
	}
	
	function getEntry($args){
		
		if(count($args)<1){
			throw new SOYCMS_Exception("index.html");
		}else{
			//ページは使わない
			$args = array_map(create_function('$a','return rawurldecode($a);'),$args);
			$uri = implode("/",$args);
			try{
				$entry = $this->dao->getByUri($uri,$this->getDirectoryId());
			}catch(Exception $e){
				throw new SOYCMS_NotFoundException("entry");
			}
		}
		
		
		//check valid entry
		if(!$this->checkEntry($entry)){
			throw new SOYCMS_Exception("entry");
		}

		return $entry;
	}
	
	function getDirectoryId(){
		return $this->getPageObject()->getId();
	}

	function build($args){
		
		$this->dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		//エントリーの取得
		$entry = $this->getEntry($args);
		
		
		//非表示の記事の場合
		if(!isset($_GET["preview"]) && $entry->isOpen() != true){
			throw new SOYCMS_EntryCloseException();
		}
		
		$this->setEntryTitle($entry->getTitle());
		
		//block:entry
		$this->buildEntryBlock($entry);
		
		//公開記事のみとする
		$this->dao->setMode("open");
		
		//ディレクトリの情報
		$this->addLabel("directory_name",array(
			"soy2prefix" => "cms",
			"text" => $this->getPageObject()->getName()
		));
		$this->addLink("directory_link",array(
			"soy2prefix" => "cms",
			"link" => soycms_get_page_url($this->getPageObject()->getUri())
		));
		
		//記事のタイトル
		$this->addLabel("entry_title",array(
			"text" => $entry->getTitle(),
			"soy2prefix" => "cms",
		));
		
		//次の記事 前の記事
		$this->buildNavigationBlock($entry);
		
		
		//ページャナビゲーション
		$this->buildPageNavigation($entry);
		
		
		/* コメント関連 */
		
		$this->buildCommentBlock($entry);
		$this->buildTrackbackBlock($entry);
		
		/* configの上書き */
		$config = $this->getPageObject()->getConfigObject();
		$config["keyword"] = $entry->getAttribute("keyword");
		$config["description"] = $entry->getAttribute("description");
		$this->getPageObject()->setConfigObject($config);
	
	}
	
	/**
	 * 子ページナビゲーションの構築
	 */
	function buildPageNavigation(){
		$entry = $this->entry;
		
		//記事にとっての次のページは次の記事とは別のもの
		//次のページ
		//前のページ
		$pages = $this->getPages($entry);
		$this->createAdd("pager","SOYCMS_DetailPageBase_HTMLPager",array(
			"pages" => $pages,
			"link" => soycms_get_page_url($this->getPageObject()->getUri()),
			"current" => $entry->getId(),
			"soy2prefix" => "block",
			"childPrefix" => "cms",
			"visible" => $this->isItemVisible("default:pager") && (count($pages)>1)
		));
		
		$this->addModel("child_entry_exists",array(
			"visible" => (count($pages)>0),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("child_entry_list","DetailPage_ChildEntryListComponent",array(
			"list" => $pages,
			"current" => $this->entry->getId(),
			"soy2prefix" => "block"
		));
	}
	
	/**
	 * 次の記事、前の記事の構築
	 */
	function buildNavigationBlock($entry){
		$dao = $this->dao;
		$pageUri = $this->getPageObject()->getUri();
		
		try{
			$prev = $dao->getPrevEntry($entry);
		}catch(Exception $e){
			$prev = null;
		}
		
		try{
			$next = $dao->getNextEntry($entry);
		}catch(Exception $e){
			$next = null;
		}
		
		$this->addModel("prev_entry_link_wrap",array("visible" => (!is_null($prev)), "soy2prefix" => "cms"));
		$this->addModel("next_entry_link_wrap",array("visible" => (!is_null($next)), "soy2prefix" => "cms"));
		
		$this->addLink("prev_entry_link",array("link" => soycms_get_page_url($pageUri,(($prev)?$prev->getUri():"")), "soy2prefix" => "cms"));
		$this->addLink("next_entry_link",array("link" => soycms_get_page_url($pageUri,(($next)?$next->getUri():"")), "soy2prefix" => "cms"));
		
		$this->addLabel("prev_entry_title",array("text"=>(($prev)?$prev->getTitle():""),"soy2prefix"=>"cms"));
		$this->addLabel("next_entry_title",array("text"=>(($next)?$next->getTitle():""),"soy2prefix"=>"cms"));
		
	}
	
	/**
	 * コメント一覧
	 * コメントフォーム
	 * の構築
	 */
	function buildCommentBlock($entry){
		
		//block:comment_list
		$this->createAdd("comment_list","SOYCMS_CommentListComponent",array(
			"entry" => $this->isItemVisible("default:comment_list") ? $entry : null,
			"soy2prefix" => "block",
			"visible" => $this->isItemVisible("default:comment_list")
		));
		
		//block:comment_form
		$this->createAdd("comment_form","SOYCMS_CommentForm",array(
			"soy2prefix" => "block",
			"comment" => $this->comment,
			"visible" => $entry->getAllowComment() && $this->isItemVisible("default:comment_form")
		));
		
		//cms:comment_allowed
		$this->addModel("comment_allowed",array(
			"visible" => $entry->getAllowComment(),
			"soy2prefix" => "cms"
		));
		//cms:comment_not_allowed
		$this->addModel("comment_not_allowed",array(
			"visible" => !$entry->getAllowComment(),
			"soy2prefix" => "cms"
		));
		
		//cms:comment_posted
		$this->addModel("comment_posted",array(
			"visible" => (isset($_GET["comment_posted"]) && $_GET["comment_posted"] > 0),
			"soy2prefix" => "cms"
		));
	}
	
	function buildTrackbackBlock($entry){
		//block:comment_list
		$this->createAdd("trackback_list","SOYCMS_TrackbackListComponent",array(
			"entry" => $entry,
			"soy2prefix" => "block"
		));
		
		
		$this->addLabel("trackback_url",array(
			"text" => soycms_get_page_url($this->getPageObject()->getUri(),$this->entry->getUri() . "?trackback"),
			"soy2prefix" => "cms",
			"visible" => $entry->getAllowTrackback()
		));
		
		$this->addInput("trackback_url_input",array(
			"value" => soycms_get_page_url($this->getPageObject()->getUri(),$this->entry->getUri() . "?trackback"),
			"soy2prefix" => "cms",
		));
		
		//cms:comment_allowed
		$this->addModel("trackback_allowed",array(
			"visible" => $entry->getAllowTrackback(),
			"soy2prefix" => "cms"
		));
		//cms:comment_not_allowed
		$this->addModel("trackback_not_allowed",array(
			"visible" => !$entry->getAllowTrackback(),
			"soy2prefix" => "cms"
		));
	}
	
	function doPost(){
		$this->dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		$entry = $this->getEntry($this->getArguments());
		
		//コメント投稿
		if(isset($_GET["comment"])){
			
			$comment = SOY2::cast("SOYCMS_EntryComment",$_POST);
			$comment->setEntryId($entry->getId());
			
			try{
				$this->comment = $comment;
				
				$url = soycms_union_uri($this->getPageObject()->getUri(),$entry->getUri());
				
				if($entry->getAllowComment()){
					$comment->setSubmitDate(time());
					$comment->save();
					$url .= "?comment_posted=" . $comment->getId() . "#comment";
				}
				
				
				SOY2PageController::redirect($url);
			
			}catch(Exception $e){
				
			}
			
			
		}
		
		//トラックバック送信
		if(isset($_GET["trackback"])){
			
			$trackback = new SOYCMS_EntryTrackback();
			$trackback->setEntryId($this->entry->getId());
			@$trackback->setUrl($_POST['url']);
			@$trackback->setBlogName($_POST['blog_name']);
			@$trackback->setExcerpt($_POST['excerpt']);
			@$trackback->setTitle($_POST['title']);
			$trackback->setSubmitdate(time());

			try{
				$trackback->save();
				$res = true;
			}catch(Exception $e){
				//失敗
				$res = false;
			}
			
			if($res !== false){
				$replyData = '<?xml version="1.0" encoding="utf-8"?><response><error>0</error><message>successful</message></response>';
			}else{
				$replyData = '<?xml version="1.0" encoding="utf-8"?><response><error>1</error><message>failed</message></response>';
			}

			header('Content-Type: text/xml');
			echo $replyData;
			
			exit;
			
			
		}
	}
	
	/**
	 * 子記事を取得
	 */
	function getPages($entry){
		
		$parent = ($entry->getParent()) ? $entry->getParent() : $entry->getId();
		$this->dao->setMode("open");
		$entries = $this->dao->getChildEntriesMap($parent);
		
		return $entries;
	}

}

class SOYCMS_AliasPageBase extends SOYCMS_DetailPageBase{
	
	function getDirectoryId(){
		return $this->getPageObject()->getObject()->getDirectory();
	}
	
	function checkEntry($entry){
		
		if($entry->getDirectory() != $this->getPageObject()->getObject()->getDirectory()){
			return false;
		}
		
		$this->entry = $entry;
		
		return true;
	}


}

class SOYCMS_DetailPageBase_HTMLPager extends SOYCMS_HTMLPager{
	
	private $keys;
	private $pages = array();
	private $current;
	private $link = "";
	
	function init(){
		$this->keys = array_keys($this->pages);
	}
	
	function execute(){
		
		$next = $this->getNextParam();
		$next["soy2prefix"] = "cms";
		$this->createAdd("next_link","HTMLLink",$next);
		$this->createAdd("next_link_wrap","HTMLModel",array(
				"visible" => $next["visible"],
				"soy2prefix"=>"cms"
		));
		$prev = $this->getPrevParam();
		$prev["soy2prefix"] = "cms";
		$this->createAdd("prev_link","HTMLLink",$prev);
		$this->createAdd("prev_link_wrap","HTMLModel",array(
				"visible" => $prev["visible"],
				"soy2prefix"=>"cms"
		));
		
		$this->createAdd("pager_list","SOY2HTMLPager_List",$this->getPagerParam());
		
		SOYBodyComponentBase::execute();	
	}
	
	function getNextParam(){
		$nextKey = array_search($this->current,$this->keys)+1; 
		$next = (isset($this->keys[$nextKey])) ? $this->pages[$this->keys[$nextKey]] : null;
		
		return array(
			"visible" => $nextKey < count($this->keys),
			"link" => ($next) ? soycms_union_uri($this->link,$next->getUri()) : ""
		);
	}
	
	function getPrevParam(){
		$prevKey = array_search($this->current,$this->keys)-1; 
		$prev = (isset($this->keys[$prevKey])) ? $this->pages[$this->keys[$prevKey]] : null;
		
		return array(
			"visible" => $prevKey >= 0,
			"link" => ($prev) ? soycms_union_uri($this->link,$prev->getUri()) : ""
		);
	}
	
	function getPagerParam(){
		$list = array();
		$currentLink = null;
		$index = 1;
		foreach($this->pages as $obj){
			if($obj->getId() == $this->current){
				$currentLink = soycms_union_uri($this->link,$obj->getUri());
			}
			$list[] = array(
						soycms_union_uri($this->link,$obj->getUri())
						,$index
					);
			$index++;
		}
		
		return array(
			"url" => "",
			"current" => $currentLink,
			"list" => $list,
			"visible" => count($this->keys) > 0,
			"soy2prefix" => "cms",
			"childSoy2Prefix" => "cms"
		);
	}
	

	function getPages() {
		return $this->pages;
	}
	function setPages($pages) {
		$this->pages = $pages;
	}

	function getCurrent() {
		return $this->current;
	}
	function setCurrent($current) {
		$this->current = $current;
	}
	
	function setLink($link){
		$this->link = $link;
	}
}

class DetailPage_ChildEntryListComponent extends SOYCMS_EntryListComponent{
	
	private $current = null;
	
	function populateItem($entity,$key){
		
		if($entity->getId() == $this->current){
			$entity->setUri(null);
			$entity->setDirectory(null);
		}
		
		$this->addModel("current_entry",array(
			"visible" => $entity->getId() == $this->current,
			"soy2prefix" => "cms"
		));
		$this->addModel("not_current_entry",array(
			"visible" => $entity->getId() != $this->current,
			"soy2prefix" => "cms"
		));
		
		
		parent::populateItem($entity,$key);
	}
	
	function getCurrent() {
		return $this->current;
	}
	function setCurrent($current) {
		$this->current = $current;
	}
}
?>