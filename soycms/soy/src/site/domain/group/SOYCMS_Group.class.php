<?php
/**
 * グループ
 * @table soycms_group
 */
class SOYCMS_Group extends SOY2DAO_EntityBase{
	
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
	
	/**
	 * @column group_description
	 */
	private $description;
	
	/**
	 * @column group_type
	 */
	private $type = "default";
	
	/**
	 * @column group_config
	 */
	private $config;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function check(){
		if(strlen($this->groupId)<1)return false;
		if(strlen($this->name)<1){
			$this->name = $this->groupId;
		}
		
		return true;
	}
	
	/* getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
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
		$this->config = $config;
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
}


/**
 * @entity SOYCMS_Group
 */
abstract class SOYCMS_GroupDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_Group $bean);

	abstract function update(SOYCMS_Group $bean);	
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByGroupId($id);
	
	/**
	 * @index id
	 * @order group_id
	 */
	abstract function get();
	
	/**
	 * @index id
	 * @order group_id
	 */
	abstract function getByType($type);
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		$binds[":updateDate"] = time();
		return array($sql,$binds);
	}
	
}
