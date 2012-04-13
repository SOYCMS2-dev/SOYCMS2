<?php
SOY2::import("site.domain.item.SOYCMS_TemplateItem");
SOY2::imports("site.domain.page.*");

class SOYCMS_Template implements SerialziedEntityInterface{
	
	public static function getTemplateGroup($groupId){
		$dirPath = self::getTemplateDirectory() . $groupId . "/";
		$filepath = $dirPath . "group.ini";
		if(!file_exists($filepath))throw new Exception("not found");
		
		$array = @parse_ini_file($filepath);
		if(!$array)throw new Exception("not found");
		
		$array["id"] = $groupId;
			
		return $array;
	}
	
	
	/**
	 * 全てのテンプレートを取得
	 */
	public static function getList($targetDir = null){
		$dir = ($targetDir) ? $targetDir : self::getTemplateDirectory();
		$list = array();
		
		$files = soy2_scandir($dir);
		foreach($files as $file){
			if(!is_dir($dir . $file))continue;
			$template = self::load($file,$targetDir);
			if($template){
				if(is_array($template)){
					foreach($template as $key => $_template){
						$list[$_template->getId()] = $_template;
					}
				}else{
					$list[$template->getId()] = $template;
				}
			}
		}
		
		//order
		$tmp = array();
		$orders = SOYCMS_DataSets::get("template.order",array());
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
		$keys = SOYCMS_DataSets::get("template.keys",array());
		if($keys != array_keys($list)){
			SOYCMS_DataSets::put("template.keys",array_keys($list));
		}
		
		return $list;
	}
	
	public static function getListByType($type){
		$templates = self::getList();
		
		foreach($templates as $key => $template){
			if($template->getType() != $type){
				unset($templates[$key]);
			}
		}
		
		return $templates;
	}
	
	/**
	 * 読み込む
	 */
	public static function load($_dir,$targetDir = null){
		$targetDir = ($targetDir) ? $targetDir : self::getTemplateDirectory();
		$dir = $targetDir . $_dir;
		
		//group
		if($_dir[0] == "_" && strpos($_dir,"/")===false){
			
			$group = null;
			if(file_exists($dir . "/group.ini")){
				$group = $_dir;
			}
			
			$files = soy2_scandir($dir);
			$list = array();
			foreach($files as $file){
				if(!is_dir($dir . "/" . $file))continue;
				$template = self::load($_dir . "/" . $file,$targetDir);
				if(!$template)continue;
				if($group)$template->setGroup($group);
				
				if($template){
					if(is_array($template)){
						foreach($template as $key => $_template){
							$list[$_template->getId()] = $_template;
						}
					}else{
						$list[$template->getId()] = $template;
					}
				}
			}
			return $list;
		}
		
		//駄目な場合
		if(!file_exists($dir . "/template.html")
		|| !file_exists($dir . "/template.ini")
		){
			return false;
		}
		
		$obj = new SOYCMS_Template();
		$obj->setId($_dir);
		$array = parse_ini_file($dir . "/template.ini",true);
		if(@$array["type"])$obj->setType(@$array["type"]);
		if(@$array["type-text"])$obj->setTypeText(@$array["type-text"]);
		$obj->setName(@$array["name"]);
		$obj->setDescription(@$array["description"]);
		if(@$array["borderColor"])$obj->setBorderColor($array["borderColor"]);
		if(@$array["templates"])$obj->setTemplateTypes($array["templates"]);
		
		if(isset($array["items"])){
			$itemIds = explode(",",$array["items"]);
			$items = array();
			
			foreach($itemIds as $itemId){
				if(!isset($array[$itemId]))continue;
				$itemArray = $array[$itemId];
				
				$item = new SOYCMS_TemplateItem();
				$item->setTemplateId($obj->getId());
				$item->setId($itemId);
				$item->setType(@$itemArray["type"]);
				$item->setLayout(@$itemArray["layout"]);
				$item->setOrder(@$itemArray["order"]);
				$item->prepare($obj);
				
				$items[$itemId] = $item;
			}
			$obj->setItems($items);
		}
		
		$obj->setCreateDate(filectime($dir . "/template.ini"));
		$obj->setUpdateDate(filemtime($dir . "/template.ini"));
		$obj->setProperties(@parse_ini_file($obj->getPropertyFilePath()));
		
		
		return $obj;
	}
	
	public static function getTemplateDirectory(){
		if(SOYCMSConfigUtil::get("template_dir")){
			return SOYCMSConfigUtil::get("template_dir");
		}
		
		$dir = SOYCMS_SITE_DIRECTORY . ".template/";
		if(!file_exists($dir)){
			soy2_mkdir($dir,0755);
		}
		return $dir;
	}
	
	public static function remove($id){
		if(strpos($id,".")!==false)return;
		$dir = self::getTemplateDirectory();
		if(is_dir($dir . $id)){
			soy2_delete_dir($dir . $id);
		}
	}
	
	/**
	 * 保存する
	 */
	function save(){
		//重複の無い値
		if(!$this->id)$this->id = "tpl_" . date("YmdHis");
		
		//dir
		$dir = self::getTemplateDirectory() . $this->id . "/";
		if(!file_exists($dir)){
			soy2_mkdir($dir,0755);
		}
		
		//template
		if(strlen($this->template)>0){
			if($this->getTemplateType() == $this->getId()){
				file_put_contents($dir . "template.html",$this->getTemplate());
			}else{
				$type = $this->getTemplateType();
				file_put_contents($dir . "{$type}.html",$this->getTemplate());
			}
		}
		
		//ini
		$content = (file_exists($dir . "template.ini")) ? parse_ini_file($dir . "template.ini",true) : array();
		$content["name"] = $this->getName();
		$content["description"] = $this->getDescription();
		$content["type"] = $this->getType();
		$content["borderColor"] = $this->getBorderColor();
		if(strlen($this->getTypeText())>0){
			$content["type-text"] = $this->getTypeText();
		}
		
		$itemIds = array();
		foreach($this->items as $key => $obj){
			if(strlen($obj->getType())<0)continue;
			
			//idのチェック
			if(preg_match('/[\/\:*?"<>|#{}%&~\*]/',$obj->getId())){
				unset($this->items[$key]);
				continue;
			}
			
			$itemIds[] = $obj->getType() . ":" . $obj->getId();
		}
		if(count($itemIds)>0)$content["items"] = implode(",",$itemIds);
		
		
		foreach($this->items as $key => $obj){
			if(strlen($obj->getType())<0)continue;
			if($key[0] == ":")continue;
			$content[$obj->getType() . ":" . $obj->getId()] = array(
				"type" => $obj->getType(),
				"layout" => $obj->getLayout(),
				"order" => $obj->getOrder()
			);
		}
		
		//templatesを並び替える
		if(isset($content["templates"])){
			
		}
		
		soy2_write_ini($dir . "template.ini",$content);
		
		file_put_contents($dir . "layout.json",json_encode($this->getLayout()));
		
	}
	
	public static function getTypes(){
		return array(
			"detail"	=> "記事ディレクトリ",
			"list"		=> "記事一覧",
			"search"	=> "検索",
			"app"		=> "アプリケーション",
			"default"	=> "ページ",
			".error"	=> "エラー"
		);
	}
	
	function setTypeText($text){
		$this->typeText = $text;
	}
	
	function getTypeText(){
		$types = self::getTypes();
		return (isset($types[$this->getType()])) ? @$types[$this->getType()] : $this->typeText;
	}
	
	function getItem($key){
		return (isset($this->items[$key])) ? $this->items[$key] : null;
	}
	
	function loadTemplate($type = "template"){
		$dir = self::getTemplateDirectory() . $this->getId();
		$type = str_replace(array(".","/","\\"),"",$type);
		$this->setTemplate(@file_get_contents($dir . "/{$type}.html"));
		return $this->getTemplate();
	}
	
	function getDefaultBlocks(){
		$type = $this->getType();
		if($type[0] == ".")$type = substr($type,1);
		$class = "SOYCMS_" . ucfirst($type) . "Page";
		
		$items = array();
		
		if(class_exists($class)){
			$defaults = array();
			$eval = '$defaults' ." = ${class}::getDefaultBlocks();";
			eval($eval);
			
			foreach($defaults as $key => $default){
				if(!isset($items["default:" . $key])){
					$item = new SOYCMS_HTMLItem();
					$item->setId($key);
					$items["default:" . $key] = $item;
				}
				$items["default:" . $key]->setId($key);
				$items["default:" . $key]->setType("default");
				$items["default:" . $key]->setName($key);
				$items["default:" . $key]->setComment($default);
			}
			
		}
		
		return $items;
	}
	
	/**
	 * レイアウトをふくんでいるかどうか
	 */
	function hasLayout($layoutId){
		$template = $this->getTemplate();
		$res =  (preg_match('/<!--\s+layout:'.$layoutId.'\s+-->/',$template)
			 && preg_match('/<!--\s+\/layout:'.$layoutId.'\s+-->/',$template));
			 
		return $res;
	}
	
	/**
	 * レイアウトの部分のHTMLを取得
	 */
	function getTemplateByLayout($layoutId){
		$template = $this->getTemplate();
		preg_match('/<!--\s+layout:'.$layoutId.'\s+-->/',$template,$tmp1,PREG_OFFSET_CAPTURE);
		preg_match('/<!--\s+\/layout:'.$layoutId.'\s+-->/',$template,$tmp2,PREG_OFFSET_CAPTURE);
		$start = $tmp1[0][1] + strlen($tmp1[0][0]);
		$end = $tmp2[0][1];
		
		$html = substr($template,$start,$end-$start);
		return $html;
	}
	
	/**
	 * レイアウトの部分のHTMLを設定
	 */
	function setTemplateByLayout($layoutId,$html){
		$template = $this->getTemplate();
		preg_match('/<!--\s+layout:'.$layoutId.'\s+-->/',$template,$tmp1,PREG_OFFSET_CAPTURE);
		preg_match('/<!--\s+\/layout:'.$layoutId.'\s+-->/',$template,$tmp2,PREG_OFFSET_CAPTURE);
		
		$start = $tmp1[0][1] + strlen($tmp1[0][0]);
		$end = $tmp2[0][1];/*  - strlen($tmp2[0][0]); */
   	
		$tmp = substr($template,0,$start);
		$tmp .= $html;
		$tmp .= substr($template,$end);
		
		$this->setTemplate($tmp);
	}
	 
	/**
	 * このテンプレートのブロックをすべて取得
	 */
	function getBlocks(){
		$dir = SOYCMS_Template::getTemplateDirectory() . $this->getId() . "/block/";
		$files = @soy2_scandir($dir);
		
		$blocks = array();
		foreach($files as $dirname){
			$id = str_replace(".block","",$dirname);
			$block = SOYCMS_Block::load($id,$dir);
			if($block){
				$blocks[$id] = $block;
			}
		}
		
		return $blocks;
	}
	
	function getPropertyFilePath(){
		$path = SOYCMS_Template::getTemplateDirectory() . $this->getId() . "/properties.ini";
		return $path;
	}
	
	function setId($id) {
		if(strpos($id,"/")!==false && $id[0] != "_"){
			$id = "_" . $id;
		}
		
		$this->id = $id;
	}
	
	function getContent(){
		if($this->getTemplateType() != $this->getId()){
			return $this->loadTemplate($this->getTemplateType());
		}
		return $this->loadTemplate();
	}
	
	function setContent($html){
		$this->setTemplate($html);
	}
	
	
	/* property */
	
	private $id;
	private $type = "detail";
	private $typeText = "";
	private $name;
	private $description = "";
	private $items = array();
	private $properties = array();
	private $template;
	private $layout;
	private $createDate;
	private $updateDate;
	private $borderColor = "#cccccc";
	
	/* 2.0.7 追加 */
	private $group = null;
	private $templateType = null;
	private $templateTypes = array();
	
	/* getter setter */
	
	function getId() {
		return $this->id;
	}
	
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getName() {
		if(strlen($this->name) < 1)return $this->getId();
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

	function getLayout() {
		if(is_null($this->layout)){
			$dir = self::getTemplateDirectory() . $this->id . "/";
			if(file_exists($dir . "/layout.json")){
				$obj = (array)json_decode(@file_get_contents($dir . "/layout.json"));
				$this->setLayout($obj);
			}else{
				$this->layout = array();
			}
		}
		return $this->layout;
	}
	function setLayout($layout) {
		if(!is_array($layout)){
			$layout = @json_decode($layout);
		}
		
		if(is_array($layout)){
			foreach($layout as $key => $value){
				$layout[$key] = (array)$value;
			}
			$this->layout = $layout;
		}
	}
	
	function setProperty($str){
		if(!is_null($str) && strlen($str) > 0){
			$dir = self::getTemplateDirectory() . $this->getId();
			file_put_contents($this->getPropertyFilePath(),$str);
		}
	}
	
	function loadProperties($str){
		$dir = self::getTemplateDirectory() . $this->getId();
		$this->setProperties(@parse_ini_file($this->getPropertyFilePath()));
	}

	function getProperties() {
		if(!is_array($this->properties))$this->properties = array();
		return $this->properties;
	}
	function setProperties($property) {
		$this->properties = $property;
	}

	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	
	function getBorderColor() {
		return $this->borderColor;
	}
	function setBorderColor($borderColor) {
		$this->borderColor = $borderColor;
	}
	
	function getGroup() {
		return $this->group;
	}
	function setGroup($group) {
		$this->group = $group;
	}
	
	function getTemplateType(){
		return (!$this->templateType) ? $this->getId() : $this->templateType;
	}
	
	function setTemplateType($type){
		if($type == $this->getId())return;
		$this->templateType = str_replace(array(".","-","/"),"",$type);
	}
	
	function getHistoryKey(){
		if($this->templateType){
			return $this->id . "!" . $this->templateType;
		}
		return $this->id;
	}
	
	public function getTemplateTypes($flag = false){
		if($flag && count($this->templateTypes) > 0){
			return array_merge(array($this->getId() => $this->getName()),$this->templateTypes);
		}
		return $this->templateTypes;
	}
	
	public function setTemplateTypes($templateTypes){
		$this->templateTypes = $templateTypes;
		return $this;
	}
	
}
?>