<?php
/**
 * 管理者とGroupの紐付け
 * @table soycms_admin_group
 */
class SOYCMS_AdminGroup extends SOY2DAO_EntityBase{
	
	/**
	 * @column id
	 */
	private $id;
	
	/**
	 * @column admin_id
	 */
	private $adminId;
	
	/**
	 * @column group_id
	 */
	private $groupId;
	
	/* getter  setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getAdminId() {
		return $this->adminId;
	}
	function setAdminId($adminId) {
		$this->adminId = $adminId;
	}
	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
}


/**
 * @entity SOYCMS_AdminGroup
 */
abstract class SOYCMS_AdminGroupDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_AdminGroup $bean);

	abstract function update(SOYCMS_AdminGroup $bean);	
	
	abstract function delete($id);
	
	abstract function deleteByGroupId($groupId);
	
	abstract function deleteByAdminId($adminId);
	
	/**
	 * @return columns_admin_id
	 */
	abstract function getByGroupId($groupId);
	
	/**
	 * @return columns_group_id
	 */
	abstract function getByAdminId($adminId);
	
	abstract function get();
	
}