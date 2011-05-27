<?php
class page_entry_editor extends SOYCMS_WebPageBase{
	
	private $sections;
	private $entry;
	private $dir;
	
	function init(){
		if(isset($_GET["entryId"])){
			
			$this->entry = SOY2DAO::find("SOYCMS_Entry",$_GET["entryId"]);
			
			try{
				$this->dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
			}catch(Exception $e){
				$this->dir = new SOYCMS_Page();
			}
			
			
		}
		if(isset($_GET["template_id"])){
			
			$this->entry = SOY2DAO::find("SOYCMS_EntryTemplate",$_GET["template_id"]);
			
			try{
				$this->dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
			}catch(Exception $e){
				$this->dir = new SOYCMS_Page();
			}
			
		}
	}
	
	function doPost(){
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if($this->entry && !$session->checkPermission($this->entry->getDirectory(),true)){
			echo "permission error";
			exit;
		}
		
		if(isset($_POST["memo"])){
			return $this->saveMemo($_POST["memo"]);
		}
		
		if(isset($_POST["author"])){
			return $this->saveAuthor(
				$_POST["author"],
				@$_POST["author_link_text"],
				@$_POST["author_link_url"],
				@$_POST["flag"]);
		}
		
		if(isset($_POST["tags"])){
			return $this->saveTags($_POST["tags"]);
		}
		
		if(isset($_POST["labels"])){
			return $this->saveLabels($_POST["labels"]);
		}
		
		if(isset($_POST["uri"])){
			return $this->saveUri($_POST["uri"]);
		}
		
		if(isset($_POST["save_autosave_config"])){
			return $this->saveAttributes(array("autosave" => $_POST["save_autosave_config"]));
		}
		
		if(isset($_POST["make_thumbnail_config"])){
			return $this->saveMakeThumbnailConfig(
				$_POST["make_thumbnail_config"],
				$_POST["thumbnail_size_l"],
				$_POST["thumbnail_size_m"],
				$_POST["thumbnail_size_s"]
			);
		}
		
		if(isset($_POST["resize_auto_config"])){
			return $this->saveResizeAutoConfig(
				$_POST["resize_auto_config"],
				$_POST["resize_auto_width"],
				$_POST["resize_auto_height"]
			);
		}
		
		if(isset($_POST["create_date"]) && isset($_POST["save_create_date"])){
			return $this->saveCreateDate($_POST["create_date"]);
		}
		
		if(isset($_POST["keyword"]) && isset($_POST["description"])){
			return $this->saveAttributes(array(
				"keyword" => $_POST["keyword"],
				"description" => $_POST["description"]
			));
		}
		
		if(isset($_POST["comment"]) && isset($_POST["trackback"]) && isset($_POST["send_ping"])){
			return $this->saveCommentTrackbackConfig(
				$_POST["comment"],
				$_POST["trackback"],
				$_POST["send_ping"],
				$_POST["feed_entry"]
			);
		}
		
		if(isset($_POST["send_trackback"])){
			return $this->sendTrackback(@$_POST["destination"]);
		}
		
		if(isset($_POST["save_open_period"])){
			$this->saveOpenPeriod($_POST["OpenPeriod"]["from"],$_POST["OpenPeriod"]["until"]);
			return;
		}
		
		if(!isset($_POST["key"]) && !isset($_POST["mode"])){
			echo "error";
			exit;
		}
		
		$key = @$_POST["key"];
		$type = $_POST["section"];
		$snippet = $_POST["snippet"];
		$values = @$_POST["values"];
		
		$section = SOYCMS_EntrySection::getSection($key,$type);
		$section->setType($type);
		$section->setValue($values);
		
		if($snippet){
			
			$snippet = SOYCMS_Snippet::load($snippet);
			if($snippet){
				
				$section->setSnippet($snippet->getId());
				
				//配列に変換
				parse_str($values,$values);
				
				//本体を取得
				$content = $snippet->loadContent($values);
				
				$section->setContent($content);
			}
		}
		
		//build section
		$section->build();
		
		if($values){
			$section->setValues($values);
		}
		
		$this->sections = array();
		$this->sections[$key] = $section;
		
		//content mode
		if(isset($_POST["mode"]) && $_POST["mode"] == "content"){
			echo $section->getContent();
			exit;
		}
		
	}
	
	function page_entry_editor($args) {
		if(count($args)){
			$this->setId("section_list");
			$this->sections = $args[0];
		}
		
		WebPage::WebPage();
		
	}
	
	 function saveAuthor($author,$text,$link,$flag){
		
		if($flag){
			$session = SOY2Session::get("base.session.UserLoginSession");
			
			$user = SOY2DAO::find("SOYCMS_User",$session->getId());
			$user->setName($author);
			$config = $user->getConfigArray();
			$config["link_text"] = $text;
			$config["link_url"] = $link;
			$user->setConfig($config);
			$user->save();
			
			
			$session->setName($user->getName());
			
			//null
			$this->entry->setAuthor(null);
			$this->entry->save();
				
		}else{
		
			$this->entry->setAuthor($author);
			$this->entry->save();
			
			SOYCMS_EntryAttribute::put($this->entry->getId(),"author_link_text",$text);
			SOYCMS_EntryAttribute::put($this->entry->getId(),"author_link_url",$link);
		
		}
		
		
		echo "Saved. " . date("H:i:s");
		exit;
		
	}
	
	function saveMemo($memo){
		
		$this->entry->setMemo($memo);
		$this->entry->save();
		echo "Saved. " . date("H:i:s");
		exit;
		
	}
	
	/**
	 * サムネイルを作るのオン、オフ
	 * サイト単位で保存する
	 */
	function saveMakeThumbnailConfig($config,$sizeL,$sizeM,$sizeS){
		SOYCMS_DataSets::put("make_thumbnail",$config);
		SOYCMS_DataSets::put("thumbnail_size.l",$sizeL);
		SOYCMS_DataSets::put("thumbnail_size.m",$sizeM);
		SOYCMS_DataSets::put("thumbnail_size.s",$sizeS);
		exit;
	}
	
	/**
	 * 最大サイズでリサイズ
	 */
	function saveResizeAutoConfig($config,$width,$height){
		SOYCMS_DataSets::put("resize_image",$config);
		SOYCMS_DataSets::put("resize_image.width",$width);
		SOYCMS_DataSets::put("resize_image.height",$height);
		exit;
	}
	
	
	/**
	 * @success output saved time
	 * @failed -1
	 */
	function saveUri($uri){
		
		try{
			$this->entry->setUri($uri);
			$this->entry->save();
			
			try{
				$dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
			}catch(Exception $e){
				$dir = new SOYCMS_Page();
			}
			
			echo rawurldecode(soycms_union_uri(soycms_get_page_url($dir->getUri()),$uri));
			
		}catch(Exception $e){
			echo 0; 
		}
		
		exit;
	}
	
	function saveTags($tags){
		//タグ付け
		$tags = str_replace("\n"," ",$tags);
		$tags = explode(" ",$tags);
		$tags = array_diff($tags,array(""));
		
		if(empty($tags)){
			//自動生成
			SOYCMS_Tag::autoTags($this->entry->getId(), $this->entry->getContent());
		}else{
			SOYCMS_Tag::updateTags($this->entry->getId(),$tags);
		}
		
		$tags = SOYCMS_Tag::getByEntryId($this->entry->getId());
		foreach($tags as $tag){
			echo "<li>" . $tag->getTag() . "</li>";
		}
		exit;
	}
	
	/**
	 * ラベルを保存
	 */
	function saveLabels($labelIds){
		$id = $this->entry->getId();
		SOYCMS_Label::putLabels($id,$labelIds);
		
		exit;
	}
	
	/**
	 * 作成日を保存
	 */
	function saveCreateDate($createDate){
		$this->entry->setCreateDate($createDate);
		$this->entry->save();
		$cdate = strtotime(implode(" ",$createDate));
		echo date("Y-m-d H:i:s",$cdate);
		exit;
	}
	
	/**
	 * meta情報を追加
	 */
	function saveAttributes($attributes){
		$id = $this->entry->getId();
		foreach($attributes as $key => $value){
			SOYCMS_EntryAttribute::put($id,$key,$value);
		}
		
		echo "Saved" . date("Y-m-d H:i:s");
		exit; 
	}
	
	/**
	 * コメント許可、トラックバック許可
	 */
	function saveCommentTrackbackConfig($comment,$trackback,$ping,$feed){
		$this->entry->setAllowComment($comment);
		$this->entry->setAllowTrackback($trackback);
		$this->entry->setIsFeed($feed);
		$this->entry->save();
		
		SOYCMS_EntryAttribute::put($this->entry->getId(),"send_ping",$ping);
	}
	
	/**
	 * トラックバックを送信する
	 */
	function sendTrackback($destination){
		
		//公開していない記事は不可
		if($this->entry->getPublish() < 1){
			echo "[error]公開していない記事からは送信出来ません";
			exit;
		}
		
		if(strlen($destination)<1){
			echo "[ERROR]送信先が指定されていません";
			exit;
		}
		
		//宛先を保存する
		SOYCMS_EntryAttribute::put($this->entry->getId(),"trackback_destination",$destination);
		
		//データの準備
		try{
			$page = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			echo "[error]";
			exit;
		}
		
		//POSTデータ
		$data = array(
			"title" => $this->entry->getTitle(),
			"url" => soycms_get_page_url($page->getUri(),$this->entry->getUri()),
			"blog_name" => SOYCMS_DataSets::get("site_name",""),
			"excerpt" => mb_strimwidth(
					strip_tags($this->entry->getContent()),0,100,"...","UTF-8"
				),
		);
		$data = http_build_query($data, "", "&");
		
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
		
		
		//送信実行
		$list = explode("\n",$destination);
		foreach($list as $url){
			$url = trim($url);
			if(!preg_match('/^https?:\/\//',$url))continue;
			echo "send to \"<i>" . $url . "</i>\"";
			$xml = file_get_contents($url, false, stream_context_create($context));
			$res = @simplexml_load_string($xml);
			if(!$res){
				echo "<br /><b style='color:red;'>...failed</b><br />";
				continue;
			}
			
			if($res->error == 0){
				echo "<br /><b>success!</b><br />";
			}else{
				echo "<br /><b style='color:red;'>...failed</b><br />";
			}
		}
		
		exit;
		
			
	}
	
	function saveOpenPeriod($from,$until){
		
		$from = implode(" ",$from);
		$until = implode(" ",$until);
		$_from = (strlen($from) > 4) ? strtotime($from) : null;
		$_until = (strlen($until) > 4) ? strtotime($until) : null;
		
		$this->entry->setOpenFrom($_from);
		$this->entry->setOpenUntil($_until);
		$this->entry->save();
		
		echo $this->entry->getOpenPeriodText();
		exit;
	}
	
	function main(){
		$this->createAdd("section_list","SectionList",array(
			"list" => $this->sections
		));
	}
	
	function getLayout(){
		return "blank.php";
	}
}
 
/**
 * @class SectionList
 * @generated by SOY2HTML
 */
class SectionList extends HTMLList{
	protected function populateItem($entity,$key){
		
		$this->addInput("section_type",array(
			"name" => "section[$key][type]",
			"value" => $entity->getType()
		));
		
		$this->addInput("section_snippet",array(
			"name" => "section[$key][snippet]",
			"value" => $entity->getSnippet()
		));
		
		$this->addInput("section_value",array(
			"name" => "section[$key][value]",
			"value" => $entity->getValue()
		));
		
		$this->addInput("section_remove",array(
			"name" => "section[$key][remove]",
			"value" => 0
		));
		
		$classes = array("m-area","liq-area","html-editor");
		if($entity->getType() == "wysiwyg"){
			$classes[] = "aobata_editor";
		}else if($entity->getType() == "preview"){
			$classes[] = "aobata_display";
		}else{
			$classes[] = "aobata_preview";
		}
		
		$this->addTextarea("section_area",array(
			"name" => "section[$key][content]",
			"value" => $entity->getContent(),
			"attr:class" => implode(" ",$classes),
			"style" => ($entity->getSectionHeight()) ? "height:" . $entity->getSectionHeight() . "px" : ""
		));
		
		$this->addModel("is_wysiwyg",array(
			"visible" => $entity->getType() == "wysiwyg"
		));
		
	}
	
	function getPreviewHTML($html){
		
		$html = preg_replace("/<script/","<!--",$html);
		$html = preg_replace("/\/script>/","-->",$html);
		
		return $html;
	}
}

?>