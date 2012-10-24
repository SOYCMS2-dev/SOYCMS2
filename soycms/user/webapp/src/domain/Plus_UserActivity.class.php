<?php
/**
 * @table plus_user_activity
 */
class Plus_UserActivity extends SOY2DAO_EntityBase{
	
	/**
	 * @return Plus_UserActivity
	 * @param userId
	 * @param type
	 * @param text(optional)
	 * @param link(optional)
	 */
	public static function prepare($userId,$type,$title,$text = null,$link = null){
		$obj = new Plus_UserActivity();
		$obj->setUserId($userId);
		$obj->setType($type);
		$obj->setTitle($title);
		if($text)$obj->setText($text);
		if($link)$obj->setLink($link);
		
		return $obj;
	}
	
	/**
	 * @return boolean
	 */
	function check(){
		$this->submitDate = time();
		$this->text = str_replace("\n","",$this->text);
		return true;
	}
	
	/**
	 * @param key1
	 * @param key2
	 * @param key3
	 */
	function keys($key1,$key2=null,$key3=null){
		$this->setKey1($key1);
		if($key2)$this->setKey2($key2);
		if($key3)$this->setKey3($key3);
	}
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column activity_link
	 */
	private $link; //リンク先
	
	/**
	 * @column activity_type
	 */
	private $type = "entry";
	
	/**
	 * @column activity_title
	 */
	private $title;
	
	/**
	 * @column activity_text
	 * NAMEとかCOUNTとかは置換出来る
	 */
	private $text = "";
	
	/**
	 * @column activity_attributes
	 */
	private $attributes;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column activity_key1
	 */
	private $key1;
	
	/**
	 * @column activity_key2
	 */
	private $key2;
	
	/**
	 * @column activity_key3
	 */
	private $key3;
	
	/* getter setter */
	
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link = $link;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getText() {
		return $this->text;
	}
	function setText($text) {
		$this->text = $text;
	}
	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	function getSubmitDate() {
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}

	function getKey1() {
		return $this->key1;
	}
	function setKey1($key1) {
		$this->key1 = $key1;
	}
	function getKey2() {
		return $this->key2;
	}
	function setKey2($key2) {
		$this->key2 = $key2;
	}
	function getKey3() {
		return $this->key3;
	}
	function setKey3($key3) {
		$this->key3 = $key3;
	}

	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
}


/**
 * @entity Plus_UserActivity
 */
abstract class Plus_UserActivityDAO extends Plus_UserDAOBase{
	
	/**
	 * @return id
	 */
	abstract function insert(Plus_UserActivity $obj);
	abstract function update(Plus_UserActivity $obj);
	abstract function delete($id);
	abstract function deleteByKey1($key1);
	abstract function deleteByKey2($key2);
	abstract function deleteByKey3($key3);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	abstract function getByUserId($userId);
	
	/**
	 * return array
	 * @order submit_date desc
	 */
	function listByKeys($keys1,$keys2 = null,$keys3 = null){
		$query = $this->getQuery();
		
		if(!empty($keys1)){
			$query->where = "(activity_key1 IN (".implode(",",$keys1)."))";
		}
		
		if(!empty($keys2)){
			$query->where = "(activity_key2 IN (".implode(",",$keys2)."))";
		}
		
		if(!empty($keys3)){
			$query->where = "(activity_key3 IN (".implode(",",$keys3)."))";
		}
		
		$res = $this->executeQuery($query);
		$result = array();
		foreach($res as $row){
			$result[] = $this->getObject($row);
		}
		return $result;
	}
}