<?php
/**
 * @table soycms_admin_role
 */
class SOYCMS_Role extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;

	/**
	 * @column admin_id
	 */
	private $adminId;
	
	private $role = "super";
	
	public static function getRoles(){
		$roles = SOY2DAO::find("SOYCMS_Role");
		
		$array = array();
		foreach($roles as $role){
			if(!isset($array[$role->getAdminId()])){
				$array[$role->getAdminId()] = array();
			}
			$array[$role->getAdminId()][$role->getRole()] = $role;
		}
		
		return $array;
	}
	
	public static function getAdmin($_role = null){
		$roles = SOY2DAO::find("SOYCMS_Role");
		
		$array = array();
		foreach($roles as $role){
			if(!isset($array[$role->getRole()])){
				$array[$role->getRole()] = array();
			}
			$array[$role->getRole()][] = $role->getAdminId();
		}
		
		if(isset($_role)){
			return (isset($array[$_role])) ? $array[$_role] : array();
		}
		
		return $array;
	}
	
	function check(){
		if(!$this->adminId)return false;
		if(!$this->role)return false;
		
		return true;
	}
	
	/* getter setter */


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
	function getRole() {
		return $this->role;
	}
	function setRole($role) {
		$this->role = $role;
	}
}

/**
 * @entity SOYCMS_Role
 */
abstract class SOYCMS_RoleDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_Role $bean);

	abstract function update(SOYCMS_Role $bean);	
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @index id
	 */
	abstract function get();
	
	/* 追加分 */
	
	/**
	 * @query #adminId# = :adminId AND #role# = :role
	 */
	abstract function getByParams($adminId,$role);
	
	abstract function deleteByAdminId($adminId);
	
	/**
	 * @order id
	 * @index role
	 */
	abstract function getByAdminId($adminId);
	
	/**
	 * @final
	 */
	function setRoles($adminId,$array){
		$role = new SOYCMS_Role();
		$role->setAdminId($adminId);
		foreach($array as $value){
			$role->setId(null);
			$role->setRole($value);
			$this->insert($role);
		}
	}
	
}
?>