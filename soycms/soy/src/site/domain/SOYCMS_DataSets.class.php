<?php
/**
 * @table soycms_data_sets
 */
class SOYCMS_DataSets {

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
		$data = new SOYCMS_DataSets();
		$data->setClassName($class);
		$data->setObject(serialize($obj));

		$dao = SOY2DAOFactory::create("config.SOYCMS_DataSetsDAO");
		try{
			$dao->clear($class);
		}catch(Exception $e){
			
		}
		$dao->insert($data);
		
		if(!isset($GLOBALS["SOYCMS_DataSets"])){
			$GLOBALS["SOYCMS_DataSets"] = array();
		}
		
		$GLOBALS["SOYCMS_DataSets"][$class] = $obj;
	}
	
	/**
	 * 値を取得し、保持する（何回か呼び出す時に便利）
	 */
	public static function load($class){
		static $_array;
		if(!$_array)$_array = array();
		
		if(!isset($_array[$class])){
			$res = self::get($class,null);
			$_array[$class] = $res;
		}
		
		return $_array[$class]; 
	}

	public static function get($class,$onNull = false){

		try{
			$dao = SOY2DAOFactory::create("SOYCMS_DataSetsDAO");
			$data = $dao->getByClass($class);

			$res = unserialize($data->getObject());
			if($res === false)throw new Exception();
			
			$GLOBALS["SOYCMS_DataSets"][$class] = $res;

			return $res;

		}catch(Exception $e){
			if($onNull !== false){
				return $onNull;
			}


			throw $e;
		}
	}
	
	public static function delete($class){
		$dao = SOY2DAOFactory::create("SOYCMS_DataSetsDAO");
		$dao->clear($class);
	}

}

/**
 * @entity SOYCMS_DataSets
 */
abstract class SOYCMS_DataSetsDAO extends SOY2DAO{
	abstract function insert(SOYCMS_DataSets $bean);
	
	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);
	
	/**
	 * @query class_name = :class
	 * @query_type delete
	 */
	abstract function clear($class);
	
	abstract function get();
}
?>