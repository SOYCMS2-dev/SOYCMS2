<?php
SOY2::import("site.domain.item.SOYCMS_NavigationItem");

/**
 * ナビゲーション情報
 */
class SOYCMS_Navigation implements SerialziedEntityInterface{

	/**
	 * 全てのライブラリを取得
	 */
	public static function getList($targetDir = null){
		$dir = ($targetDir) ? $targetDir : self::getNavigationDirectory();
		
		$list = array();
		
		$files = soy2_scandir($dir);
		
		foreach($files as $file){
			if(!is_dir($dir . $file))continue;
			$navigation = self::load($file,$targetDir);
			if($navigation){
				$list[$navigation->getId()] = $navigation;
			}
		}
		
		//order
		$tmp = array();
		$orders = SOYCMS_DataSets::get("navigation.order",array());
		foreach($orders as $order){
			if(!isset($list[$order]))continue;
			$tmp[$order] = $list[$order];
		}
		foreach($list as $key => $template){
			if(!isset($tmp[$key])){
				$tmp[$key] = $template;
			}
		}
		$list = $tmp;
		
		//save keys
		SOYCMS_DataSets::put("navigation.keys",array_keys($list));
		
		return $list;
	}
	
	/**
	 * 読み込む
	 */
	public static function load($dir,$targetDir = null){
		$dir = ($targetDir) ? $targetDir . $dir : self::getNavigationDirectory() . $dir;
		
		if(!file_exists($dir . "/template.html")
		|| !file_exists($dir . "/navigation.ini")
		){
			return false;
		}
		
		$obj = new SOYCMS_Navigation();
		$obj->setId(basename($dir));
		$array = parse_ini_file($dir . "/navigation.ini",true);
		$obj->setName(@$array["name"]);
		$obj->setDescription(@$array["description"]);
		if(isset($array["templates"]))$obj->setTemplates($array["templates"]);
		
		if(isset($array["items"])){
			$itemIds = explode(",",$array["items"]);
			$items = array();
			
			foreach($itemIds as $itemId){
				if(!isset($array[$itemId]))continue;
				$itemArray = $array[$itemId];
				$item = new SOYCMS_NavigationItem();
				$item->setNavigationId($obj->getId());
				$item->setId($itemId);
				$item->setType(@$itemArray["type"]);
				$item->setLayout(@$itemArray["layout"]);
				$item->setOrder(@$itemArray["order"]);
				$item->prepare($obj);
				
				$items[$itemId] = $item;
			}
			$obj->setItems($items);
		}
		
		$obj->setCreateDate(filectime($dir . "/navigation.ini"));
		$obj->setUpdateDate(filemtime($dir . "/navigation.ini"));
		
		return $obj;
	}
	
	public static function getNavigationDirectory(){
		if(SOYCMSConfigUtil::get("library_dir")){
			return SOYCMSConfigUtil::get("library_dir");
		}
		
		$dir = SOYCMS_SITE_DIRECTORY . ".navigation/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		return $dir;
	}
	
	public static function remove($id){
		if(strpos($id,".")!==false)return;
		$dir = self::getNavigationDirectory();
		if(is_dir($dir . $id)){
			soy2_delete_dir($dir . $id);
		}
	}
	
	/**
	 * 保存する
	 */
	function save(){
		//重複の無い値
		if(!$this->id)$this->id = "navi_" . date("YmdHis");
		
		//dir
		$dir = self::getNavigationDirectory() . $this->id . "/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		
		//navigation
		if(strlen($this->getTemplate())>0){
			if($this->getTemplateType() == "template"){
				file_put_contents($dir . "template.html",$this->getTemplate());
			}else{
				$type = $this->getTemplateType();
				$type = str_replace(array(".","/","\\"),"",$type);
				file_put_contents($dir . "{$type}.html",$this->getTemplate());
			}
		}
	
		
		//ini
		$content = @parse_ini_file($dir . "/navigation.ini",true);
		$content["name"] = $this->getName();
		$content["description"] = $this->getDescription();
		
		$itemIds = array();
		foreach($this->items as $key => $obj){
			$itemIds[] = $obj->getType() . ":" . $obj->getId();
		}
		if(count($itemIds)>0)$content["items"] = implode(",",$itemIds);
		
		foreach($this->items as $key => $obj){
			$array = array();
			$array["type"] = $obj->getType();
			$array["order"] = $obj->getOrder();
			$array["layout"] = $obj->getLayout();
			$content[$obj->getType() . ":" . $obj->getId()] = $array;
		}
		
		if($this->getTemplateType() != "template"){
			if(!isset($content["templates"]))$content["templates"] = array(
				"template" => "template",
				$this->getTemplateType() => $this->getTemplateType()
			);
		}
		
		
		soy2_write_ini($dir . "navigation.ini",$content);
		
	}
	
	function getItem($key){
		return (isset($this->items[$key])) ? $this->items[$key] : null;
	}
	
	function loadTemplate(){
		if(!$this->id)return;
		
		$dir = self::getNavigationDirectory() . $this->id . "/";
		if(!file_exists($dir))return "";
		
		$type = $this->getTemplateType();
		$type = str_replace(array(".","/","\\"),"",$type);
		$this->setTemplate(@file_get_contents($dir . "/{$type}.html"));
		return $this->getTemplate();
	}
	
	/**
	 * プレビューを取得
	 * PHPコードがあることもあるので、置換する
	 * この段階でHTMLを読み込むので注意が必要
	 */
	function getPreview(){
		$content = $this->loadTemplate();
		$content = preg_replace("/<\?php.*?>/mi","_____________",$content);
		return $content;
	}
	
	function getLayout(){
		return array(
			$this->getId() => array(
				"name" => $this->getName(),
				"color" => "#CFCCCC"
			)
		);
	}
	
	/**
	 * このテンプレートのブロックをすべて取得
	 */
	function getBlocks(){
		$dir = SOYCMS_Navigation::getNavigationDirectory() . $this->getId() . "/block/";
		
		if(!is_dir($dir))return array();
		$files = soy2_scandir($dir);
		
		$blocks = array();
		foreach($files as $dirname){
			$id = str_replace(".block","",$dirname);
			$blocks[$id] = SOYCMS_Block::load($id,$dir);
		}
		
		return $blocks;
	}
	
	/**
	 * 標準ブロックを返す
	 *  Navigationは現状無し
	 */
	function getDefaultBlocks(){
		return array();
	}
	
	function getContent(){
		return $this->loadTemplate();
	}
	
	function setContent($html){
		$this->setTemplate($html);
	}
	
	/* 以下保持パラメータ */
	
	private $id;
	private $name;
	private $description;
	private $items = array();
	private $template;
	private $createDate;
	private $updateDate;
	
	private $templateType = "template";
	private $templates = array();
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		if(empty($this->name))return $this->getId();
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getItems() {
		return $this->items;
	}
	function setItems($items) {
		$this->items = $items;
	}
	function getTemplate() {
		return $this->template;
	}
	function setTemplate($template) {
		$this->template = $template;
	}
	function getCreateDate() {
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

	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}

	public function getTemplateType(){
		return $this->templateType;
	}

	public function setTemplateType($templateType){
		if(!isset($this->templates[$this->templateType])){
			return $this;
		}
		$this->templateType = $templateType;
		return $this;
	}
	
	public function getTemplateTypeText(){
		if(!isset($this->templates[$this->templateType])){
			return null;
		}
		return $this->templates[$this->templateType];
	}

	public function getTemplates(){
		return $this->templates;
	}

	public function setTemplates($templates){
		$this->templates = $templates;
		return $this;
	}
	

	function getHistoryKey(){
		if($this->templateType && $this->templateType != "template"){
			return $this->id . "!" . $this->templateType;
		}
		return $this->id;
	}
}

?>