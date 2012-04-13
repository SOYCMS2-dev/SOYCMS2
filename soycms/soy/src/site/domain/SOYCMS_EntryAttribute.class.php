<?php
/**
 * @table soycms_entry_attribute
 */
class SOYCMS_EntryAttribute {

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column entry_id
	 */
	private $entryId;

	/**
	 * @column class_name
	 */
	private $className;

	/**
	 * @column object_data
	 */
	private $object;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getClassName() {
		return $this->className;
	}
	function setClassName($className) {
		$this->className = $className;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}

	public static function put($entryId,$class,$obj){
		$data = new SOYCMS_EntryAttribute();
		$data->setEntryId($entryId);
		$data->setClassName($class);
		
		if(is_numeric($obj) || is_string($obj)){
			$data->setObject($obj);
		}else{
			$data->setObject(soy2_serialize($obj));
		}

		$dao = SOY2DAOContainer::get("config.SOYCMS_EntryAttributeDAO");
		$dao->clearByParams($entryId,$class);
		
		//nullの時は削除する
		if($obj !== null){
			$dao->insert($data);
		}
	}

	public static function get($entryId,$class,$onNull = false){
		
		try{
			$dao = SOY2DAOContainer::get("SOYCMS_EntryAttributeDAO");
			$data = $dao->getByParams($entryId,$class);
			
			$object = $data->getObject();
			if(is_numeric($object)){
				return $object;
			}
			
			if(empty($object)){
				throw new Exception();
			}
			
			if(!preg_match('/^[a-zA-Z]:\d+/',$object)){
				return $object;
			}
			
			$res = soy2_unserialize($object);
			if($res === false)throw new Exception();

			return $res;

		}catch(Exception $e){
			if($onNull !== false){
				return $onNull;
			}


			throw $e;
		}
	}
	
	public static function getByEntryId($entryId){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryAttributeDAO");
		$data = $dao->getByEntryId($entryId);
		
		$res = array();
		
		foreach($data as $obj){
			$res[$obj->getClassName()] = $obj->getObject();
		}
		return $res;
	}
	
	public static function delete($entryId,$class){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryAttributeDAO");
		$dao->clearByParams($entryId,$class);
	}


	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
}

/**
 * @entity SOYCMS_EntryAttribute
 */
abstract class SOYCMS_EntryAttributeDAO extends SOY2DAO{
	abstract function insert(SOYCMS_EntryAttribute $bean);
	
	/**
	 * @return object
	 * @query #entryId# = :entryId AND class_name = :class
	 */
	abstract function getByParams($entryId,$class);
	
	/**
	 * @query class_name = :class AND object_data = :data
	 */
	abstract function getByValues($class,$data);
	
	/**
	 * @query entry_id = :entryId AND class_name = :class
	 * @query_type delete
	 */
	abstract function clearByParams($entryId,$class);
	
	abstract function deleteByEntryId($entryId);
	
	abstract function get();
	
	/**
	 * @order class_name desc
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @query_type update
	 * @columns #className#,#object#
	 * @query #className# = :className
	 */
	abstract function toggleByClassName($className,$object);
		
}
?>