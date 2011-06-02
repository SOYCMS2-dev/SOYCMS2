<?php
SOY2::imports("site.domain.page.*");
SOY2::import("site.domain.item.SOYCMS_PageItem");

/**
 * @table soycms_site_page
 */
class SOYCMS_Page extends SOY2DAO_EntityBase{
	
	//default	通常ページ
	//detail	記事ディレクトリ
	//alias		エイリアス
	//.feed		XML
	//.error	エラー
	//list		一覧
	//search	検索
	//app		SOY Appとの連携
	
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column parent
	 */
	private $parent;
	
	private $name;	//ページ名称
	private $uri;	//url
	
	/**
	 * @column page_type
	 */
	private $type = "default";
	
	/**
	 * @column page_config
	 */
	private $config;
	private $_config;
	
	/**
	 * @no_persistent
	 */
	private $object;	//serialized object
	
	private $template = null;
	
	/**
	 * @column is_deleted
	 */
	private $deleted = false;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	/**
	 * @no_persistent
	 */
	private $itemConfig = array();
	
	/**
	 * @no_persistent
	 */
	private $properties = array();

	/**
	 * 有効なデータかチェック
	 */
	function check(){
		//uriのフォーマット
		$this->uri = preg_replace('/\/+/',"/",$this->uri);
		$this->uri = preg_replace('/(^\/|[^a-zA-Z0-9\-_\.\/])/',"_",$this->uri);
		if(strlen($this->uri)<1)return false;
		
		return true;
	}
	
	/**
	 * ページObjectのクラス名を返す
	 */
	function getPageObjectClassName(){
		$classes = array(
			"default" => "SOYCMS_SitePage",
			"detail" => "SOYCMS_DetailPage",
			"alias" => "SOYCMS_AliasPage",
			"list" => "SOYCMS_ListPage",
			"search" => "SOYCMS_SearchPage",
			"app" => "SOYCMS_ApplicationPage",
			".feed" => "SOYCMS_FeedPage",
			".error" => "SOYCMS_ErrorPage",
		);

		return (isset($classes[$this->type])) ? $classes[$this->type] : "SOYCMS_SitePage";
	}
	
	/**
	 * ページオブジェクトを取得
	 */
	function getPageObject(){
		return $this->getObject();
	}
	
	/**
	 * 設定ファイルクラスなどを格納するディレクトリ
	 */
	function getPageDirectory(){
		$class = str_replace(array("-","/","."),"_",$this->getUri());
		$dir = SOYCMS_SITE_DIRECTORY . ".page/" . $class . "/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		return $dir;
	}
	
	/**
	 * オブジェクトの設定ファイル
	 */
	function getConfigFilePath(){
		return $this->getPageDirectory() . "object.ini"; 
	}
	
	/**
	 * 実行するクラスファイル名
	 */
	function getCustomClassName(){
		$class = str_replace(array("-","/","."),"_",$this->getUri());
		if(is_numeric($class[0]))$class = "page_" . $class;
		return $class . "_page";
	}
	
	/**
	 * 実行するクラスファイルのベースクラス名
	 */
	function getBaseClassName(){
		return $this->getPageObjectClassName() . "Base";
	}
   
	
	/**
	 * Build WebPage Object
	 */
	function getWebPageObject($args){
		if(file_exists($this->getPageDirectory() . "class.php")){
			include_once($this->getPageDirectory() . "class.php");

			$obj = SOY2HTMLFactory::createInstance($this->getCustomClassName(),array(
				"arguments" => array("page" => $this, "arguments" => $args)
			));
		}else{
			$obj = SOY2HTMLFactory::createInstance($this->getBaseClassName(),array(
				"arguments" => array("page" => $this, "arguments" => $args)
			));
		}
		
		return $obj;
	}	
	
	/**
	 * 種別のテキスト
	 */
	function getTypeText(){
		$types = array(
			"default" => "ページ",
			"detail" => "記事ディレクトリ",
			"list" => "記事一覧",
			"alias" => "ディレクトリ(エイリアス)",
			"search" => "検索",
			"app" => "アプリケーション",
			".feed" => "フィード",
			".error" => "エラー" 
		);

		return $types[$this->type];
	}
	
	/**
	 * @return boolean
	 */
	function isUseCustomTemplate(){
		return file_exists($this->getPageDirectory() . "template.html");
	}
	
	/**
	 * テンプレートのパスを取得
	 */
	function getTemplateFilePath(){
		if($this->isUseCustomTemplate()){
			return $this->getPageDirectory() . "template.html";
		}else{
			return SOYCMS_Template::getTemplateDirectory() . $this->template . "/template.html";
		}
	}
	
	/**
	 * Itemを取得
	 */
	function getItems($type = null){
		$isUseCustomTemplate = $this->isUseCustomTemplate(); 
		$items = array();
		if(!$isUseCustomTemplate){
			$template = SOYCMS_Template::load($this->getTemplate());
			if(!$template){
				$template = new SOYCMS_Template();
			}
			
			$items = $template->getItems();
		}
		
		if($type){
			foreach($items as $key => $item){
				if($item->getType() != $type){
					unset($items[$key]);
				}
			}
		}
		
		return $items;
	}
	
	/**
	 * 要素設定を読み込む
	 * ファイルから読み込むので一度だけ実行
	 */
	function loadItemConfig(){
		if($this->itemConfig){
			return $this->itemConfig;
		}
		
		$dir = $this->getPageDirectory();
		if(file_exists($dir . "items.ini")){
			$this->itemConfig = parse_ini_file($dir . "items.ini",true);
		}
		return $this->itemConfig;
	}
	
	/**
	 * 要素設定を保存する
	 * @param array
	 */
	function saveItemConfig($array){
		$dir = $this->getPageDirectory();
		soy2_write_ini($dir . "items.ini",$array);
	}
	
	function isDirectory(){
		return ($this->getType() == "detail");
	}
	
	/**
	 * index.htmlのUriを取得
	 */
	function getIndexUri(){
		$uri = $this->getUri();
		if($uri == "_home"){
			$uri = "";
		}else if($uri[strlen($uri)-1] != "/"){
			$uri .= "/";
		}
		$uri .= "index.html";
		
		return $uri;
	}
	
	/**
	 * 親ディレクトリのuriを取得
	 */
	function getParentDirectoryUri(){
		$uri = $this->getUri();
		if($uri == "_home"){
			return $uri;
		}else{
			$uri = dirname($uri);
			if($uri == ".")$uri = "_home";
		}
		
		return $uri;
	}

	
	function getProperties(){
		if(empty($this->properties)){
			$dir = $this->getPageDirectory();
			if(file_exists($dir . "properties.ini")){
				$this->properties = parse_ini_file($dir . "properties.ini",true);	
			}
		}
		return $this->properties;
	}
	
	function setProperties($array){
		if(is_array($array)){
			$dir = $this->getPageDirectory();
			soy2_write_ini($dir . "properties.ini",$array);
		}
	}
	
	/**
	 * URIは/で終わらないように。
	 */
	function setUri($uri) {
		if($uri[strlen($uri)-1]=="/")$uri=substr($uri,0,strlen($uri)-1);
		$this->uri = $uri;
	}
	
	/* getter setter */
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getUri() {
		return $this->uri;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(!is_string($config)){
			return $this->setConfigObject($config);
		}else{
			$this->config = $config;
		}
	}
	function setConfigParam($key,$value){
		$config = $this->getConfigObject();
		$config[$key] = $value;
		$this->setConfig($config);
	}
	function getConfigParam($key){
		$config = $this->getConfigObject();
		return @$config[$key];
	}
	function getConfigObject() {
		if(!$this->_config){
			$tmp = @soy2_unserialize($this->config);
			$type = $this->getType();
			if($type[0] == "."){
				$type = "default";
			}
			$object = (object)array(
					"content-type" => "text/html",
					"encoding" => "UTF-8",
					"title" => "#PageName# - #DirName# - #SiteName#",
					"openPeriodStart" => null,
					"openPeriodEnd" => null,
					"public" => 1	,			//基本公開
					"icon" => $type . ".gif",
					"favicon" => "",
					"order" => 10
			);
			
			switch($this->type){
				case "detail":
					$object->title = "#EntryTitle# - #DirName# - #SiteName#";
					break;
				default:
					break;
			}
			
			
			//index.htmlの時
			if(strpos($this->getUri(),"index.html") !== false){
				$object->order = 1;
				$object->title = "#PageName# - #SiteName#";
			}
			
			//HOME直下の場合
			if($this->type != "detail" && dirname($this->getUri()) == "."){
				$object->title = "#PageName# - #SiteName#";
			}
			
			
			
			if($tmp){
				SOY2::cast($object,(object)$tmp);
			}
			$this->_config = (array)$object;
			$this->config = soy2_serialize($this->_config);
			
		}
		return $this->_config;
	}
	function setConfigObject($_config) {
		$obj = (object)$this->getConfigObject();
		SOY2::cast($obj,$_config);
		$obj = (array)$obj;
		
		$this->_config = $obj;
		$this->config = soy2_serialize($obj);
	}
	
	/**
	 * PageObject
	 */
	function getObject() {
		if(empty($this->object)){
			$filepath = $this->getConfigFilePath();
			if(file_exists($filepath)){
				$plain = @parse_ini_file($filepath,true);
				if($plain !== false){
					$this->object = SOY2::cast($this->getPageObjectClassName(),(object)$plain);
				}
			}else{
				$class = $this->getPageObjectClassName();
				$this->object = new $class();
			}
			
			$this->object->setPage($this);
		}
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}
	function getDeleted() {
		return $this->deleted;
	}
	function setDeleted($deleted) {
		$this->deleted = $deleted;
	}
	function isDeleted(){
		return (boolean)$this->deleted;
	}
	
	function getTemplate() {
		return $this->template;
	}
	function setTemplate($template) {
		$this->template = $template;
	}

	function getCreateDate() {
		if(!$this->createDate)$this->createDate = time();
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}

	function getParent() {
		return $this->parent;
	}
	function setParent($parent) {
		$this->parent = $parent;
	}

	function getItemConfig() {
		return $this->itemConfig;
	}
	function setItemConfig($itemConfig) {
		$this->itemConfig = $itemConfig;
	}
}

/**
 * @entity SOYCMS_Page
 */
abstract class SOYCMS_PageDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_Page $bean);
	
	/**
	 * @sql insert into soycms_site_page(id,create_date,update_date) values(:id,0,0)
	 */
	function insertId($id){
		$this->executeUpdateQuery($this->getQuery(),$this->getBinds());
	}

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCMS_Page $bean);	
	
	/**
	 * @trigger onDelete
	 */
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	abstract function getByTemplate($template);
	
	/**
	 * @return object
	 */
	abstract function getByUri($uri);
	
	abstract function getByParent($parent);
	
	/**
	 * @order uri
	 * @index id
	 */
	abstract function get();
	
	/**
	 * @order id
	 * @index id
	 */
	abstract function getByType($type);
	
	/**
	 * @return column_count_page
	 * @columns count(id) as count_page
	 */
	abstract function countByType($type);
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		$binds[":updateDate"] = time();
		return array($sql,$binds);
	}
	
	/**
	 * @final
	 */
	function onDelete($sql,$binds){
		return array($sql,$binds);
	}
	
	/**
	 * @final
	 */
	function getUniqueUri($prefix = "new"){
		$counter = 1;
		while(true){
			$uri = $prefix . $counter;
			
			try{
				$this->getByUri($uri);
			}catch(Exception $e){
				break;
			}
			
			$counter++;
		}
		return $uri;
	}
}

/**
 * soycms_site_page#
 */
class SOYCMS_PageBase{
	
	private $page;
	
	function SOYCMS_PageBase(){
		
	}
	
	function getTemplateType(){
		return ($this->page) ? $this->page->getType() : "default";
	}
	
	function setPage($page){
		$this->page = $page;
	}
	function getPage(){
		return $this->page;
	}
	
	function save(){
		$array = (array)SOY2::cast("object",$this);
		$filepath = $this->page->getPageDirectory() . "object.ini";
		soy2_write_ini($filepath,$array);
	}
	
	public static function getDefaultBlocks(){
		return array();
	}
	
	
	/**
	 * 設定画面
	 * @return html
	 */
	function getConfigPage(){
		$class_name = str_replace("SOYCMS_","",get_class($this)) . "FormPage";
		$filepath = dirname(__FILE__) . "/page/" . get_class($this) . "/".$class_name.".class.php";
		
		if(!file_exists($filepath)){
			return;
		}
		include_once($filepath);
		
		$webPage = SOY2HTMLFactory::createInstance($class_name,array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}
?>