<?php

/**
 * @table plus_user_group
 */
class Plus_Group extends SOY2DAO_EntityBase {
	
	function check(){
		if(!preg_match('/^[a-zA-Z0-9\-]+$/',$this->groupId))return false;
		
		return true;
	}
	
	public static function getFieldTypes(){
		$_types = array(
			"input",
			"multi",
			"radio",
			"select",
		);
		$types = SOYCMS_ObjectCustomFieldConfig::getTypes();
		
		$res = array();
		foreach($_types as $key){
			$res[$key] = $types[$key];
		}
		
		return $res;
	}
	

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column group_id
	 */
	private $groupId;
	
	/**
	 * @column group_name
	 */
	private $name;
	
	private $parent = null;
	
	/**
	 * @column configure
	 */
	private $config;
	
	/**
	 * @no_persistent
	 */
	private $_config = null;
	
	function getConfigure($key){
		$array = $this->getConfigureArray();
		return (isset($array[$key])) ? $array[$key] : null;
	}
	
	function getConfigureArray(){
		if(!$this->_config){
			$this->_config = soy2_unserialize($this->config);
		}
		
		return (is_array($this->_config)) ? $this->_config : array();
	}
	
	function isDefault(){
		$array = $this->getConfigureArray();
		return (@$array["default"] == 1);
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
	function getParent() {
		return $this->parent;
	}
	function setParent($parent) {
		$this->parent = $parent;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(is_array($config)){
			$this->_config = $config;
			$config = soy2_serialize($config);
		}
		$this->config = $config;
	}

	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
}

/**
 * @table plus_user_user_group
 */
class Plus_UserGroup extends SOY2DAO_EntityBase{	
	
	/**
	 * @param userId
	 * @param groupIds Array<Number>
	 */
	public static function saveGroups($userId,$groupIds){
		$res = array();
		$dao = SOY2DAOFactory::create("Plus_UserGroupDAO");
		$groups = SOY2DAO::find("Plus_Group");
		
		//clear
		$dao->deleteByUserId($userId);
		
		foreach($groupIds as $groupId){
			if(!isset($groups[$groupId]))continue;
			
			$obj = new Plus_UserGroup();
			$obj->setUserId($userId);
			$obj->setGroupId($groupId);
			$dao->insert($obj);
			$res[] = $groups[$groupId]->getGroupId();
		}
		
		return $res;
	}
	
	/**
	 * @return Array<Plus_Group>
	 */
	public static function getGroupsByUserId($userId){
		$res = array();
		$dao = SOY2DAOFactory::create("Plus_UserGroupDAO");
		$groupDAO = SOY2DAOFactory::create("Plus_GroupDAO");
		
		$tmp = $dao->getByUserId($userId);
		foreach($tmp as $obj){
			$res[$obj->getGroupId()] = $groupDAO->getById($obj->getGroupId());
		}
		
		return $res;
	}
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column group_id
	 */
	private $groupId;
	
	/* getter setter */
	

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
}



/**
 * @entity Plus_Group
 */
abstract class Plus_GroupDAO extends Plus_UserDAOBase{
	
	/**
	 * @return id
	 */
	abstract function insert(Plus_Group $obj);
	abstract function update(Plus_Group $obj);
	
	/**
	 * @tigger onDelete
	 */
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByGroupId($groupId);
	
	/**
	 * @index id
	 */
	abstract function get();
	
	/**
	 * @columns count(id) as group_count
	 * @return column_group_count
	 */
	abstract function count();
	
	/**
	 * @final
	 */
	function onDelete($sql,$binds){
		SOY2DAOFactory::create("Plus_UserGroupDAO")->deleteByGroupId($binds[":id"]);
	}
	
}

/**
 * @entity Plus_UserGroup
 */
abstract class Plus_UserGroupDAO extends Plus_UserDAOBase{
	
	abstract function insert(Plus_UserGroup $obj);
	
	abstract function getByUserId($userId);
	abstract function getByGroupId($groupId);
	
	abstract function deleteByUserId($userId);
	abstract function deleteByGroupId($groupId);
	
	
} 