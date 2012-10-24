<?php
/**
 * ユーザの所有・参加を表すオブジェクト
 * @table plus_user_join
 */
class Plus_UserJoin{
	
	public static function put($userId,$key,$value,$type){
		
		$res = Plus_UserJoin::check($userId,$key,$value);
		if($res){
			return false;
		}
		
		$obj = new Plus_UserJoin();
		$obj->setUserId($userId);
		$obj->setKey($key);
		$obj->setValue($value);
		$obj->setType($type);
		
		$dao = SOY2DAOFactory::create("Plus_UserJoinDAO");
		$dao->insert($obj);
	}
	
	public static function search($userId,$key){
		$dao = SOY2DAOFactory::create("Plus_UserJoinDAO");
		$res = $dao->getUserJoinListByUser($userId,$key);
		foreach($res as $key => $obj){
			if(!$obj->getValue())unset($res[$key]);
		}
		return $res;
	}
	
	public static function check($userId,$key,$value){
		$dao = SOY2DAOFactory::create("Plus_UserJoinDAO");
		try{
			$res = $dao->getByParams($userId,$key,$value);
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	public static function get($userId,$key,$value){
		$dao = SOY2DAOFactory::create("Plus_UserJoinDAO");
		$res = $dao->getByParams($userId,$key,$value);
		return $res;
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
	 * @column join_key
	 */
	private $key;
	
	/**
	 * @column join_value
	 */
	private $value;
	
	/**
	 * 参加者としての種類(member,owner)
	 * @column join_type
	 */
	private $type;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column join_config
	 * 予備カラム
	 */
	private $config;
	
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
	function getKey() {
		return $this->key;
	}
	function setKey($key) {
		$this->key = $key;
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getCreateDate() {
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}


/**
 * @entity Plus_UserJoin
 */
abstract class Plus_UserJoinDAO extends Plus_UserDAOBase{
	
	/**
	 * @return id
	 */
	abstract function insert(Plus_UserJoin $obj);
	abstract function update(Plus_UserJoin $obj);
	abstract function delete($id);
	
	/**
	 * @columns count(distinct user_id) as count_user
	 * @return column_count_user
	 * @query #key# = :key AND #type# = :type AND #value# = :value
	 */
	abstract function countJoinByType($key,$value,$type);
	
	/**
	 * @query_type delete
	 * @query #userId# = :userId AND #key# = :key AND #value# = :value
	 */
	abstract function clearByParams($userId,$key,$value);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	
	abstract function get();
	
	abstract function getByUserId($userId);
	
	/**
	 * @query #userId# = :userId AND #key# = :key
	 */
	abstract function getUserJoinListByUser($userId,$key);
	
	/**
	 * @query #key# = :key AND #value# = :value
	 */
	abstract function getUserJoinListByValue($key,$value);
	
	/**
	 * @query #key# = :key AND #value# = :value AND #type# = :type
	 */
	abstract function getUserJoinListByType($key,$value,$type);
	
	/**
	 * @return object
	 * @query #userId# = :userId AND #key# = :key AND #value# = :value
	 */
	abstract function getByParams($userId,$key,$value);
	
}
?>