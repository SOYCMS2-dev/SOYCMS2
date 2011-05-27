<?php
class SOYCMS_HTMLItem {
	
	function __construct($object = null){
		if($object && $object instanceof SOYCMS_HTMLItem){
			$this->setId($object->getId());
			$this->setType($object->getType());
			$this->setName($object->getName());
			$this->setComment($object->getComment());
			$this->setLayout($object->getLayout());
			$this->setOrder($object->getOrder());
		}	
	}

	private $id;
	private $name;
	private $type;
	private $comment;
	private $layout;
	private $order = 0;
	protected $object;
	private $deleted;
	
	public static function getTypes(){
		return array(
			"block" => "ブロック",
			"library" => "ライブラリ",
			"navigation" => "ナビゲーション",
			"default" => "【＊】"
		);
	}
	
	function getTypeText(){
		$type = self::getTypes();
		return (isset($type[$this->getType()])) ? $type[$this->getType()] : "-";
	}
	
	/**
	 * 設定リンク
	 */
	function getConfigLink(){
		switch($this->type){
			case "library":
			case "navigation":
				return soycms_create_link("/page/" . $this->type . "/detail?id=" . $this->id . "&back=" . strtolower(preg_replace('/SOYCMS_(.*)Item/','$1',get_class($this))));
				break;
			case "default":
				return "";
				break;
		}
		
		return "";
	}
	
	/**
	 * コピーリンク
	 */
	function getCopyLink(){
		switch($this->type){
			case "library":
			case "navigation":
				return soycms_create_link("/page/" . $this->type . "/create?id=" . $this->id . "&back=" . strtolower(preg_replace('/SOYCMS_(.*)Item/','$1',get_class($this))));
				break;
			case "default":
				return "";
				break;
		}
		
		return "";
	}
	
	/**
	 * テンプレートに記載するやり方を取得
	 */
	function getFormat(){
		switch($this->type){
			case "library":
				$format = 'cms:include="'.$this->id.'"';
				break;
			case "navigation":
				$format = 'cms:navigation="'.$this->id.'"';
				break;
			case "block":
			case "default":
				$format = 'block:id="'.$this->id.'"';
				break;
			default:
				$format = 'cms:include="'.$this->id.'"';
				break;
		}
		
		return $format;
	}
	
	function check(){
		if(strlen($this->id)<1)return false;
		if(strlen($this->type)<1)return false;
		if(strlen($this->name)<1){
			$this->id = $this->name;
		}
		
		return true;
	}
	
	function generate($args,$options = array()){
		
	}
	
	function prepare(){
		
	}
	
	//取り除く
	function remove(){
		$this->prepare();
		
		if($this->getType() == "block" && $this->object){
			$this->object->delete();
		}
	} 
	
	/* getter setter */
	
	function setId($id) {
		$_id = preg_replace("/^[^:]+:/","",$id);
		$this->id = $_id;
		
		if(strpos($id,":") !== false){
			$type = preg_replace('/:[^:]+$/',"",$id);
			$this->setType($type);
		}
	}
	
	function getName() {
		if(empty($this->name))return $this->id;
		return $this->name;
	}
	
	
	/* getter setter */
	function getId() {
		return $this->id;
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
	function getComment() {
		return $this->comment;
	}
	function setComment($comment) {
		$this->comment = $comment;
	}
	function getLayout() {
		return $this->layout;
	}
	function setLayout($layout) {
		$this->layout = $layout;
	}
	function getOrder() {
		return (int)$this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getObject() {
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
}
?>