<?php

class SOYCMS_Snippet implements SerialziedEntityInterface{

	private $id;
	private $name;
	private $group;
	private $class;
	private $description;
	private $content;
	private $form;
	private $order = 0;
	private $updateDate;
	private $type = "wysiwyg";
	
	/**
	 * 全てのライブラリを取得
	 */
	public static function getList($orders = null){
		$list = self::getListByDirectory();
		
		//order
		if(!$orders){
			
			$orders = SOYCMS_DataSets::get("snippet.order",null);
			if(!$orders){
				$orders = array(
					"common_text",
					"common_heading",
					"common_image",
					"common_list",
					"common_document",
					"common_line",
					"common_box",
					"common_quote",
					"common_pre",
					"youtube",
					"common_more"
				);
			}

		}
		
		//save keys
		SOYCMS_DataSets::put("snippet.keys",array_keys($list));
		
		return self::sortSnippet($list,$orders);
	}
	
	public static function getListByDirectory($directory = null){
		$dir = ($directory) ? $directory : self::getSnippetDirectory();
		
		$list = array();
		
		$files = soy2_scandir($dir);
		
		foreach($files as $file){
			if(!is_dir($dir . $file))continue;
			$snippet = self::load($file,$directory);
			if($snippet){
				$list[$snippet->getId()] = $snippet;
			}
		}
		
		return $list;
	}
	
	public static function sortSnippet($list,$orders){
		$tmp = array();
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
		
		return $list;
	}
	
	/**
	 * 読み込む
	 */
	public static function load($dir,$directory = null){
		$dir = ($directory) ? $directory . $dir : self::getSnippetDirectory() . $dir;
		
		if(!file_exists($dir . "/template.html")
		|| !file_exists($dir . "/snippet.ini")
		){
			return false;	
		}		
		
		$obj = new SOYCMS_Snippet();
		$obj->setId(basename($dir));
		$obj->setContent(file_get_contents($dir . "/template.html"));
		$obj->setForm(@file_get_contents($dir . "/form.html"));
		$array = parse_ini_file($dir . "/snippet.ini");
		$obj->setName(@$array["name"]);
		$obj->setDescription(@$array["description"]);
		$obj->setOrder(@$array["order"]);
		$obj->setGroup(@$array["group"]);
		$obj->setClass(@$array["class"]);
		$obj->setType(@$array["type"]);
		$obj->setUpdateDate(filemtime($dir . "/snippet.ini"));
		
		return $obj;
	}
	
	public static function getSnippetDirectory(){
		$dir = SOYCMS_SITE_DIRECTORY . ".snippet/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		return $dir;	
	}
	
	public static function remove($id){
		if(strpos($id,".")!==false)return;
		$dir = self::getSnippetDirectory();
		if(is_dir($dir . $id)){
			soy2_delete_dir($dir . $id);
		}
	}
	
	/**
	 * 保存する
	 */
	function save(){
		
		//dir
		$dir = self::getSnippetDirectory() . $this->id . "/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		
		//template
		file_put_contents($dir . "template.html",$this->getContent());
		file_put_contents($dir . "form.html",$this->getForm());
		
		//ini
		$content = array();
		$content["name"] = $this->getName();
		$content["description"] = $this->getDescription();
		$content["group"] = $this->getGroup();
		$content["order"] = $this->getOrder();
		$content["class"] = $this->getClass();
		$content["type"] = $this->getType();
		
		soy2_write_ini($dir . "snippet.ini",$content);
		
	}
	
	function setType($type) {
		if(!$type)return;
		$this->type = $type;
	}
	
	/**
	 * PHPが使える用にimportする
	 */
	function loadContent($values = null){
		//dir
		$dir = self::getSnippetDirectory() . $this->id . "/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		
		//template
		ob_start();
		include($dir . "template.html");
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * フォームを取得
	 */
	function getForm($values = array()) {
		$form = $this->form;
		
		foreach($values as $key => $value){
			$form = str_repalce("#" . $key . "#", $value, $form);	
		}
		
		$form = preg_replace('/#[A-Z]+#/',"",$form);
		
		return $form;
	}
	
	/**
	 * buildForm
	 */
	function buildForm($values = array()){
		$section = SOYCMS_EntrySection::getSection("",$this->getType());
		return $section->buildForm($this->getForm($values),$values);
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

	function setForm($form) {
		$this->form = $form;
	}

	function getGroup() {
		return $this->group;
	}
	function setGroup($group) {
		$this->group = $group;
	}

	function getClass() {
		return $this->class;
	}
	function setClass($class) {
		$this->class = $class;
	}

	function getType() {
		return $this->type;
	}
}

?>