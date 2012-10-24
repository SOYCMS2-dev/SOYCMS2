<?php

/**
 * @table plus_user_user_config
 */
class Plus_UserConfig{
	
	public static function put($userId,$key,$val){
		$dao = SOY2DAOFactory::create("Plus_UserConfigDAO");
		$dao->deleteByParams($userId,$key);
		
		if(is_object($val) || is_array($val)){
			$val = soy2_serialize($val);
		}
		
		$obj = new Plus_UserConfig();
		$obj->setUserId($userId);
		$obj->setClass($key);
		$obj->setObject($val);
		$dao->insert($obj);
	}
	
	public static function get($userId,$key,$default = null){
		$dao = SOY2DAOFactory::create("Plus_UserConfigDAO");
		
		try{
			$obj = $dao->getByParams($userId,$key);
			
			$val = $obj->getObject();
			if(is_numeric($val)){
				return $val;
			}
			if(empty($val)){
				throw new Exception();
			}
			if(!preg_match('/^[a-zA-Z]:\d+/',$val)){
				return $val;
			}
			
			$_tmp = @soy2_unserialize($val);
			if($_tmp)return $_tmp;
			return $val;
		}catch(Exception $e){
			return $default;
		}
	}
	
	public static function clear($userId,$key){
		$dao = SOY2DAOFactory::create("Plus_UserConfigDAO");
		$dao->deleteByParams($userId,$key);
	}
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column class_name
	 */
	private $class;
	
	/**
	 * @column object_data
	 */
	private $object;
	
	
	/* getter setter */

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getClass() {
		return $this->class;
	}
	function setClass($class) {
		$this->class = $class;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}
}

/**
 * @entity Plus_UserConfig
 */
abstract class Plus_UserConfigDAO extends Plus_UserDAOBase{
	
	abstract function insert(Plus_UserConfig $obj);
	abstract function update(Plus_UserConfig $obj);
	
	/**
	 * @query #userId# = :userId AND #class# = :class
	 */
	abstract function deleteByParams($userId,$class);
	
	/**
	 * @index class_name
	 */
	abstract function getByUserId($userId);
	
	/**
	 * @return object
	 * @query #userId# = :userId AND #class# = :class
	 */
	abstract function getByParams($userId,$class);
	
}
?>