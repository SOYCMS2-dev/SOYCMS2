<?php
/**
 * @table admin_data_sets
 */
class SOYCMS_CommonConfig {

	/**
	 * @id
	 */
	private $id;

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

	public static function put($class,$obj){
		$data = new SOYCMS_CommonConfig();
		$data->setClassName($class);
		$data->setObject(serialize($obj));

		$dao = SOY2DAOFactory::create("config.SOYCMS_CommonConfigDAO");
		$dao->clear($class);
		
		if(!is_null($obj)){
			$dao->insert($data);
		}
	}

	public static function get($class,$onNull = false){

		try{
			$dao = SOY2DAOFactory::create("SOYCMS_CommonConfigDAO");
			$data = $dao->getByClass($class);

			$res = unserialize($data->getObject());
			if($res === false)throw new Exception();

			return $res;

		}catch(Exception $e){
			if($onNull !== false){
				return $onNull;
			}


			throw $e;
		}
	}
	
	public static function delete($class){
		$dao = SOY2DAOFactory::create("SOYCMS_CommonConfigDAO");
		$dao->clear($class);
	}

}

/**
 * @entity SOYCMS_CommonConfig
 */
abstract class SOYCMS_CommonConfigDAO extends SOY2DAO{
	abstract function insert(SOYCMS_CommonConfig $bean);
	
	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);
	
	/**
	 * @sql delete from admin_data_sets where class_name = :class
	 */
	abstract function clear($class);
	
	abstract function get();
	
	/**
	 * @final
	 */
	function getDataSource(){
		return SOY2DAO::_getDataSource(SOYCMS_DB_DSN,SOYCMS_DB_USER,SOYCMS_DB_PASS);
	}
}
?>