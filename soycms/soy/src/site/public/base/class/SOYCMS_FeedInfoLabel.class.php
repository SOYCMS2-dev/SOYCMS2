<?php
/**
 * フィードの情報を出力する
 * 何も指定しない場合、サイト全体(index.html)のフィードを出力
 * pageを指定していた場合、そのpageの所属するディレクトリのフィード情報を出力
 * テンプレートにcms:uri="hogehoge"と指定することも可能
 * 出力しない設定の場合は何も出力しない
 * 
 * 既に出力した情報を出力しないようになっています
 * @author miyazawa
 */
class SOYCMS_FeedInfoLabel extends HTMLLabel{

	private $page;
	
	public static function check($uri = null,$flag = false){
		static $_uris;
		if(!$_uris)$_uris = array();
		
		if(!in_array($uri,$_uris)){
			$_uris[] = $uri;
			return true;
		}
		
		if($flag){
			$_uris = null;
			unset($_uris);
		}
		
		return false;
	}
	
	function execute(){
		
		try{
		
			//無指定の場合(site_feed)
			if(!$this->page){
				$uri = $this->getAttribute("cms:uri");
				if(!$uri)$uri = "_home";
				
				$this->page = SOY2DAO::find("SOYCMS_Page",array("uri" => $uri));
			}else{
				if(!$this->page->isDirectory()){
					$uri = $this->page->getParentDirectoryUri();
					$this->page = SOY2DAO::find("SOYCMS_Page",array("uri" => $uri)); 
				}
			}
			
			//既に出力していた場合は無駄になるので、チェックします
			$res = self::check($this->page->getUri());
			if($res){
				//フィードの情報を生成して設定
				$html = $this->buildFeedInfo($this->page);
				$this->setHtml($html);
			}else{
				self::check(null,true); //clear
			}
		
		}catch(Exception $e){
			//do nothing on error
		}
		
		
		
		parent::execute();
	}
	
	/**
	 * フィード情報の出力
	 */
	function buildFeedInfo($page){
		if(!$page)return "";
		if(isset($_GET["template_preview"]))return "";	//テンプレートのプレビューモード
				
		$obj = $page->getPageObject();
		if(!$obj instanceof SOYCMS_DetailPage)return "";
		
		//タイトル置換用の空オブジェクトを生成
		$htmlObj = $page->getWebPageObject(array());
		
		$html = array();
		
		$config = $obj->getFeedRDF();
		if($config["output"]){
			$title = $htmlObj->convertTitle($config["title"]);
			$html[] = '<link rel="alternate" type="application/rss+xml" title="'.$title.'" href="'.soycms_get_page_url($page->getUri(),$config["uri"]).'" />';
		}
		$config = $obj->getFeedRSS();
		if($config["output"]){
			$title = $htmlObj->convertTitle($config["title"]);
			$html[] = '<link rel="alternate" type="application/rss+xml" title="'.$title.'" href="'.soycms_get_page_url($page->getUri(),$config["uri"]).'" />';
		}
		$config = $obj->getFeedAtom();
		if($config["output"]){
			$title = $htmlObj->convertTitle($config["title"]);
			$html[] = '<link rel="alternate" type="application/atom+xml" title="'.$title.'" href="'.soycms_get_page_url($page->getUri(),$config["uri"]).'" />';
		}
		
		//if(count($html)>0)$html[] = "";
		
		return implode("\n",$html);
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
}
?>