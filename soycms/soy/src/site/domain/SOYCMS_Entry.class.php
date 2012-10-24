<?php
SOY2::imports("site.domain.entry.*");
/**
 * @table soycms_site_entry
 */
class SOYCMS_Entry extends SOY2DAO_EntityBase{

	const DATE_MIN = 0;
	const DATE_MAX = 2147483647;//1970+time

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column parent_entry_id
	 */
	private $parent;
	
	private $title;
	
	/**
	 * @column title_section
	 */
	private $titleSection;
	
	private $uri;
	
	/**
	 * @column directory_uri
	 */
	private $directoryUri;
	
	private $content;
	
	private $sections;
	private $_sections;
	
	/**
	 * @column entry_publish
	 */
	private $publish = 0;	//trash:-1 close:0 open:1
	
	/**
	 * @column entry_status
	 */
	private $status = "draft";
	
	private $directory;		//entry directory
	
	/**
	 * @column display_order
	 */
	private $order = 0;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	/**
	 * 最後に同期を取った時刻
	 * @column last_update_date
	 */
	private $lastUpdateDate = -1;
	
	/**
	 * @column open_from
	 */
	private $openFrom = self::DATE_MIN;
	
	/**
	 * @column open_until
	 */
	private $openUntil = self::DATE_MAX;
	
	private $_labels;
	
	/**
	 * @column author_id
	 */
	private $authorId;
	
	private $author;
	
	//管理用メモ
	private $memo;
	
	//コメント
	/**
	 * @column allow_comment
	 */
	private $allowComment = false;
	
	/**
	 * @column allow_trackback
	 */
	private $allowTrackback = false;
	
	/**
	 * @column feed_entry
	 */
	private $isFeed = 1;
	
	private $_attributes = array();
	
	function getSectionsList(){
		if(!$this->_sections || !is_array($this->_sections)){
			$this->_sections = SOYCMS_EntrySection::unserializeSection($this->sections);
		}
		
		return $this->_sections;
	}
	
	/**
	 * contentに表示するsectionを全て取得
	 */
	function getContentSectionsList(){
		$list = $this->getSectionsList();
		$sections = array();
		foreach($list as $section){
			if($section instanceof SOYCMS_EntrySection_IntroductionSection){
				$sections = array();
				continue;
			}
			
			if($section instanceof SOYCMS_EntrySection_MoreSection){
				continue;
			}
			
			$sections[] = $section;
		}
		return $sections;
	}
	
	/**
	 * sectionからcontentを生成
	 */
	function buildContent(){
		$list = $this->getSectionsList();
		$content = "";
		foreach($list as $section){
			if($section instanceof SOYCMS_EntrySection_IntroductionSection){
				$content = "";
				continue;
			}
			
			if($section instanceof SOYCMS_EntrySection_MoreSection){
				continue;
			}
			
			$content .= $section->getContent();
		}
		return $content;
	}
	
	function setSections($sections) {
		if(!is_string($sections)){
			$this->_sections = $sections;
			$sections = SOYCMS_EntrySection::serializeSection($sections);
		}
		$this->sections = $sections;
	}
	
	function setSectionsList($sections){
		$this->_sections = $sections;
		$this->sections = SOYCMS_EntrySection::serializeSection($sections);
	}
	
	function setCreateDate($createDate) {
		if(is_array($createDate)){
			$createDate = strtotime(implode(" ",$createDate));
		}
		if(is_null($createDate))return;
		$this->createDate = $createDate;
	}
	
	function getCreateDate() {
		if(strlen($this->createDate)<1){
			$this->createDate = time();
		}
		
		return $this->createDate;
	}
	
	/**
	 * 設定されているラベルオブジェクトを取得
	 */
	function getLabels(){
		$labels = SOYCMS_Label::getByEntryId($this->getId());
		return $labels;
	}
	
	/**
	 * チェック
	 * publishのフラグとステータスを確認
	 */
	function check(){
		return true;
	}
	
	/**
	 * 添付ファイルのパス
	 */
	function getAttachmentPath(){
		$id = sprintf("%05d",$this->getId());
		if(strlen($id)>5)$id = ((int)$id / 10000) . "/" . $id;
		
		
		//アップロードディレクトリの指定
		if(defined("SOYCMS_SITE_UPLOAD_DIR")){
			$dir = SOYCMS_SITE_UPLOAD_DIR . "/" . $id . "/";
		}else{
			$dir = SOYCMS_SITE_DIRECTORY . "files/" . $id . "/";
		}
		
		if(!file_exists($dir)){
			soy2_mkdir($dir);
		}

		return $dir;
	}

	/**
	 * 添付ファイルのURL
	 */
	function getAttachmentUrl(){
		$id = sprintf("%05d",$this->getId());
		if(strlen($id)>5)$id = ((int)$id / 10000) . "/" . $id;
		
		if(defined("SOYCMS_SITE_UPLOAD_DIR")){
			$prefix = str_replace(SOYCMS_SITE_DIRECTORY,SOYCMS_SITE_URL,SOYCMS_SITE_UPLOAD_DIR);
			return soycms_union_uri($prefix ,  $id );
		}else{
			return soycms_union_uri(SOYCMS_SITE_URL, "files" , $id);
		}
	}
	
	/**
	 *  画像を指定してサムネイルを検索
	 */
	function getThumbnails($filename){
		$attachmentDir = $this->getAttachmentPath();
		$dir = $attachmentDir . "thumb/" . crc32($filename) . "/";
		if(!file_exists($dir))return array();
		
		$files = soy2_scandir($dir);
		
		$res = array();
		foreach($files as $file){
			$res[] = array(
				"file" => "thumb/" . crc32($filename) . "/" . $file,
				"size" => str_replace("_" . $filename,"",$file)
			);
		}
		
		return $res;
	}
	
	/**
	 * 公開されているか
	 */
	function isOpen(){
		if($this->publish < 1)return false;
		if($this->openFrom > time() ||$this->openUntil < time() )return false;
		
		return true;
	}
	
	/**
	 * 属性の取得
	 */
	function getAttribute($key){
		if($this->_attributes){
			return $this->_attributes[$key];
		}
		return SOYCMS_EntryAttribute::get($this->id,$key,"");
	}
	
	function getAttributes(){
		if(!$this->_attributes){
			$this->_attributes = SOYCMS_EntryAttribute::getByEntryId($this->id);
		}
		return $this->_attributes;
	}
	
	
	function isOverwrited(){
		return ($this->getStatus() == "open" && $this->getUpdateDate() > $this->getLastUpdateDate());
	}
	
	/* 公開期間周り */
	function getOpenPeriodText(){
		$from = $this->getOpenFrom(true);
		$to = $this->getOpenUntil(true);
		if(!$from && !$to)return "設定なし";
		$_from = (!$from) ? "設定なし" : date("Y-m-d H:i",$from);
		$_to = (!$to) ? "設定なし" : date("Y-m-d H:i",$to);
		return $_from . "〜" . $_to;
	}
	
	/* getter setter */

	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getParent() {
		return $this->parent;
	}
	function setParent($parent) {
		$this->parent = $parent;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getUri() {
		return $this->uri;
	}
	function setUri($uri) {
		$uri = preg_replace(array('/^[\/]+/','/\/+$/'),"",trim($uri));
		$this->uri = $uri;
	}
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
	}
	function getSections() {
		return $this->sections;
	}
	function getPublish() {
		return $this->publish;
	}
	function setPublish($publish) {
		$this->publish = $publish;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}
	function getOpenFrom($flag = false) {
		if($flag){
			return ($this->openFrom > self::DATE_MIN) ? $this->openFrom : null;
		}
		return $this->openFrom;
	}
	function setOpenFrom($openFrom) {
		if(!$openFrom)$openFrom = self::DATE_MIN;
		$this->openFrom = $openFrom;
	}
	function getOpenUntil($flag = false) {
		if($flag){
			return ($this->openUntil < self::DATE_MAX) ? $this->openUntil : null;
		}
		return $this->openUntil;
	}
	function setOpenUntil($openUntil) {
		if(!$openUntil)$openUntil = self::DATE_MAX;
		$this->openUntil = $openUntil;
	}
	function getMemo() {
		return $this->memo;
	}
	function setMemo($memo) {
		$this->memo = $memo;
	}
	function getAllowComment() {
		return (boolean)$this->allowComment;
	}
	function setAllowComment($allowComment) {
		$this->allowComment = $allowComment;
	}
	function getAllowTrackback() {
		return (boolean)$this->allowTrackback;
	}
	function setAllowTrackback($allowTrackback) {
		$this->allowTrackback = $allowTrackback;
	}

	function getTitleSection() {
		if(strlen($this->titleSection)<1)return $this->getTitle();
		return $this->titleSection;
	}
	function setTitleSection($titleSection) {
		$this->titleSection = $titleSection;
	}

	function getAuthor() {
		return $this->author;
	}
	function setAuthor($author) {
		$this->author = $author;
	}

	function getAuthorId() {
		if(!$this->authorId && defined("SOYCMS_LOGIN_USER_ID"))return SOYCMS_LOGIN_USER_ID;
		return $this->authorId;
	}
	function setAuthorId($authorId) {
		$this->authorId = $authorId;
	}

	function getLastUpdateDate() {
		return $this->lastUpdateDate;
	}
	function setLastUpdateDate($lastUpdateDate) {
		$this->lastUpdateDate = $lastUpdateDate;
	}

	

	function getIsFeed() {
		return $this->isFeed;
	}
	function setIsFeed($isFeed) {
		$this->isFeed = $isFeed;
	}

	function getDirectoryUri() {
		return $this->directoryUri;
	}
	function setDirectoryUri($directoryUri) {
		$this->directoryUri = $directoryUri;
	}
}

/**
 * @entity SOYCMS_Entry
 */
abstract class SOYCMS_EntryDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_Entry $bean);
	
	/**
	 * @sql insert into soycms_site_entry(id,entry_status,allow_comment,allow_trackback) values(:id,'draft',0,0)
	 */
	function insertId($id){
		$this->executeUpdateQuery($this->getQuery(),$this->getBinds());
	}

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCMS_Entry $bean);
	
	/**
	 * @columns #publish#,directory
	 */
	abstract function updatePublishByDirectory($publish,$directory);
	
	/**
	 * @columns #status#,directory
	 */
	abstract function updateStatusByDirectory($status,$directory);
	
	/**
	 * @columns #publish#,id
	 */
	abstract function updatePublishById($id,$publish);
	
	/**
	 * @columns #status#,id
	 */
	abstract function updateStatusById($id,$status);
	
	/**
	 * @columns #order#,id
	 */
	abstract function updateOrder($id,$order);
	
	/**
	 * @query #directory# = :directory
	 * @columns #directoryUri#
	 */
	abstract function updateDirectoryUri($directoryUri,$directory);
	
	/**
	 * @final
	 */
	function updateOrders(){
		$sql = "update soycms_site_entry set display_order = display_order+1";
		$this->executeUpdateQuery($sql);
	}
	
	/**
	 * @trigger onDelete
	 */
	abstract function delete($id);
	
	abstract function deleteByDirectory($directory);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 * @query directory = :directory AND #order# >= :order AND id <> :id
	 * @order #order#,id
	 */
	abstract function getNextEntry($entry);
	
	/**
	 * @return object
	 * @query directory = :directory AND #order# <= :order AND id <> :id
	 * @order #order# desc,id desc
	 */
	abstract function getPrevEntry($entry);
	
	/**
	 * @return object
	 * @query #uri# = :uri AND directory = :dir
	 */
	abstract function getByUri($uri,$dir);
	
	/**
	 * @order #order#
	 */
	abstract function getByParent($parent);
	
	/**
	 * @columns id,title,uri
	 * @query #parent# = :parent OR id = :parent
	 * @index id
	 */
	abstract function getChildEntriesMap($parent);
	
	/**
	 * @final
	 */
	function checkUri($uri,$dir,$id = null){
		try{
			$page = $this->getByUri($uri,$dir);
			return ($page->getId() != $id);
		}catch(Exception $e){
			return false;
		}
	}
	
	/**
	 * @columns count(id) as entry_count
	 * @return column_entry_count
	 */
	abstract function count();
	
	/**
	 * @columns count(id) as entry_count
	 * @return column_entry_count
	 */
	abstract function countByDirectory($directory);
	
	/**
	 * @columns count(id) as entry_count
	 * @return column_entry_count
	 * @query #directory# = :directory AND uri LIKE :uri
	 */
	abstract function countByUri($uri,$directory);
	
	/**
	 * @columns count(id) as entry_count
	 * @query #publish# = :status
	 * @return column_entry_count
	 */
	abstract function countByPublishStatus($status);
	
	/**
	 * @columns count(id) as entry_count
	 * @query #status# = :status
	 * @return column_entry_count
	 */
	abstract function countByStatus($status);
	
	/**
	 * @query directory = :directory AND #parent# is null AND #publish# >= 0
	 * @order #updateDate# desc
	 */
	abstract function getByDirectory($directory);
	
	/**
	 * @query directory = :directory
	 * @order #updateDate# desc
	 */
	abstract function getAllByDirectory($directory);
	
	/**
	 * @columns id
	 * @return columns_id
	 * @query directory = :directory AND #parent# is null AND #publish# >= 0
	 * @order #updateDate# desc
	 */
	abstract function listByDirectory($directory);
	
	/**
	 * @query directory = :pageId AND #parent# is null AND #publish# >= 0
	 * @order #updateDate# desc
	 * @return object
	 */
	abstract function getPageEntry($pageId);
	
	/**
	 * @query directory = :directory AND #parent# is null
	 * @order #updateDate# desc
	 */
	abstract function getByStatus($status,$publish);
	
	/**
	 * @query #publish# < 0
	 */
	abstract function getTrashEntry();
	
	/**
	 * @query directory in (<?php implode(',',:dirids) ?>)
	 */
	abstract function searchByDirectories($dirids);
	
	/**
	 * フィード用の記事を取得
	 * @query directory in (<?php implode(',',:dirids) ?>) AND feed_entry = 1
	 */
	abstract function searchFeedEntryByDirectories($dirids);
	
	/**
	 * @order id
	 */
	abstract function get();
	
	/**
	 * @order #updateDate# desc
	 */
	abstract function getRecentEntries();
	
	
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		$binds[":updateDate"] = time();
		return array($sql,$binds);
	}
	
	/**
	 * 削除時
	 * @final
	 */
	function onDelete($sql,$binds){
		$id = $binds[":id"];
		SOY2DAOContainer::get("SOYCMS_EntryAttributeDAO")->deleteByEntryId($id);
		return array($sql,$binds);
	}
	
	private $mode = "normal";
	
	/**
	 * @final
	 */
	function setMode($mode){
		$this->mode = $mode;
	}
	
	/**
	 * @final
	 * @override
	 */
	function executeQuery($query,$binds = array(),$keepStatement = false){
		
		if($this->mode == "open"){
			$this->mode = "tmp";
			$res = $this->executeOpenEntryQuery($query,$binds);
			$this->mode = "open";
			return $res;
		}
		
		return parent::executeQuery($query,$binds,$keepStatement);
	}
	
	/**
	 * @final
	 * 公開しているEntryに限定するQueryを追加
	 */
	function executeOpenEntryQuery($query,$binds){
		if(is_object($query)){
			$query = clone($query);
			if(strpos($query->where,"entry_publish = 1")===false){
				$query->where .= (strlen($query->where) > 0) ? " AND " : "";
				$query->where .= "entry_publish = 1 AND open_until >= :now AND open_from <= :now";
			}
		}
	
		$binds[":now"] = time();
		return $this->executeQuery($query,$binds);

	}
}

/**
 * SOYCMS_EntrySection Base Class
 */
class SOYCMS_EntrySection{
	
	private $name;
	private $type;
	private $value;		//保存している値
	private $content;	//HTML
	private $snippet;
	
	/**
	 * @return string
	 */
	public static function serializeSection($sections){
		
		//when null
		if(!$sections){
			return "";
		}
		
		$res = "";
		foreach($sections as $section){
			$res .= $section->convertToString() . "\n\n";
		}
		
		return $res;
	}
	
	/**
	 * @return array
	 */
	public static function unserializeSection($sections){
		$res = array();
		
		preg_match_all('/<!-- section:([a-zA-Z0-9\-_]+)(.*)-->/m',$sections,$tmp,PREG_OFFSET_CAPTURE);
		
		foreach($tmp[0] as $key => $array){
			$type = $tmp[1][$key][0];
			$start = $tmp[0][$key][1] + strlen($tmp[0][$key][0]);
			$arguments = $tmp[2][$key][0];
			
			$arguments = str_replace('value="',"\nvalue=\"",$arguments);
			$array = parse_ini_string($arguments);
			
			$regex = '/<!--.*\/section:'.$type.'.*-->/';
			preg_match($regex,$sections,$tmp2,PREG_OFFSET_CAPTURE,$start);
			$end = $tmp2[0][1];
			
			$content = substr($sections,$start,$end - $start);
			
			$section = SOYCMS_EntrySection::getSection("",$type);
			$section->setContent($content);
			
			$section->setSnippet(@$array["snippet"]);
			$section->setValue(@$array["value"]);
			
			$res[] = $section;
		}
		
		
		
		return $res;
	}
	
	public static function getSection($name,$type){
		if(class_exists("SOYCMS_EntrySection_" . ucwords($type) . "Section")){
			$class = "SOYCMS_EntrySection_" . ucwords($type) . "Section";
			$obj = new $class;
		
		//default
		}else{
			$obj = new SOYCMS_EntrySection();
		}
				
		
		if($obj instanceof SOYCMS_EntrySection){
			$obj->setName($name);
			$obj->setType($type);
			return $obj;
		}
		
		throw new Exception(get_class($obj) . " is not instance of SOYCMS_EntrySection");
		
	}
	
	/**
	 * @return string
	 */
	function convertToString(){
		
		$value = $this->getValue();
		$snippet = $this->getSnippet();
		
		$res = array();
		$res[] = "<!-- section:" . $this->getType() .
				" snippet=\"".$snippet. "\"" .
				((!empty($value)) ? " value=\"".addslashes($value)."\"" : "").
				"-->";
		$res[] = $this->getContent();
		$res[] = "<!-- /section:" . $this->getType() . " -->";
		
		return implode("\n",$res);
	}
	
	/**
	 * 処理
	 */
	function build(){
		$this->setValue(null);
	}
	
	function setValues($values){
		$content = $this->content;
		foreach($values as $_key => $value){
			$content = str_replace("#" . $_key . "#",$value,$content);
		}
		$this->content = $content;
	}
	
	/**
	 * Sectionの大きさを指定
	 */
	function getSectionHeight(){
		return null;
	}
	
	function getValue() {
		if($this->type == "wysiwyg")$this->value = null;
		return $this->value;
	}
	
	function buildForm($html,$values){
		return $html;
	}
	
	/* getter setter */

	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function setValue($value) {
		$this->value = $value;
	}
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
	}
	function getSnippet() {
		return $this->snippet;
	}
	function setSnippet($snippet) {
		$this->snippet = $snippet;
	}
}
?>