<?php

class SOYCMS_Library implements SerialziedEntityInterface{

	private $id;
	private $name;
	private $description;
	private $content;
	private $order = 0;
	private $updateDate;
	
	/**
	 * 全てのライブラリを取得
	 */
	public static function getList($targetDir = null){
		$dir = ($targetDir) ? $targetDir : self::getLibraryDirectory();
		
		$list = array();
		
		$files = soy2_scandir($dir);
		
		foreach($files as $file){
			if(!is_dir($dir . $file))continue;
			$library = self::load($file,$targetDir);
			if($library){
				$list[$library->getId()] = $library;
			}
		}
		
		//order
		$tmp = array();
		$orders = SOYCMS_DataSets::get("library.order",array());
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
		SOYCMS_DataSets::put("library.keys",array_keys($list));
		
		return $list;
	}
	
	/**
	 * 読み込む
	 */
	public static function load($dir,$targetDir = null){
		$dir = ($targetDir) ? $targetDir . $dir : self::getLibraryDirectory() . $dir;
		
		if(!file_exists($dir . "/template.html")
		|| !file_exists($dir . "/library.ini")
		){
			return false;	
		}		
		
		$obj = new SOYCMS_Library();
		$obj->setId(basename($dir));
		$obj->setContent(file_get_contents($dir . "/template.html"));
		$array = @parse_ini_file($dir . "/library.ini");
		$obj->setName(@$array["name"]);
		$obj->setDescription(@$array["description"]);
		$obj->setOrder(@$array["order"]);
		$obj->setUpdateDate(filemtime($dir . "/library.ini"));
		
		return $obj;
	}
	
	public static function getLibraryDirectory(){
		$dir = SOYCMS_SITE_DIRECTORY . ".library/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		return $dir;	
	}
	
	public static function remove($id){
		if(strpos($id,".")!==false)return;
		$dir = self::getLibraryDirectory();
		if(is_dir($dir . $id)){
			soy2_delete_dir($dir . $id);
		}
	}
	
	/**
	 * 保存する
	 */
	function save(){
		
		if(!$this->check())return false;
		
		
		//dir
		$dir = self::getLibraryDirectory() . $this->id . "/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		
		//template
		file_put_contents($dir . "template.html",$this->getContent());
		
		//ini
		$content = array();
		$content["name"] = $this->getName();
		$content["description"] = $this->getDescription();
		
		soy2_write_ini($dir . "library.ini",$content);
		
	}
	
	/**
	 * プレビューを取得
	 * 実際に表示する物と異なって良い。phpコードが含まれることもあるので、置換する
	 */
	function getPreview(){
		$content = $this->getContent();
		$content = preg_replace("/<\?php.*?>/mi","_____________",$content);
		return $content;
	}
	
	function check(){
		if(preg_match('/[@!.*<>#\',]/',$this->id))return false;
		return true;
	}
	
	/* getter setter */
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		if(!$this->name)$this->name = $this->id;
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
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

	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
}

?>