<?php
/**
 * @table soycms_page_attribute
 */
class SOYCMS_PageAttribute {

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column page_id
	 */
	private $pageId;

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

	public static function put($pageId,$class,$obj){
		$data = new SOYCMS_PageAttribute();
		$data->setPageId($pageId);
		$data->setClassName($class);
		
		if(is_numeric($obj) || is_string($obj)){
			$data->setObject($obj);
		}else{
			$data->setObject(soy2_serialize($obj));
		}

		$dao = SOY2DAOContainer::get("config.SOYCMS_PageAttributeDAO");
		$dao->clearByParams($pageId,$class);
		
		//nullの時は削除する
		if($obj !== null){
			$dao->insert($data);
		}
	}

	public static function get($pageId,$class,$onNull = false){
		
		try{
			$dao = SOY2DAOContainer::get("SOYCMS_PageAttributeDAO");
			$data = $dao->getByParams($pageId,$class);
			
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
	
	public static function getByPageId($pageId){
		$dao = SOY2DAOContainer::get("SOYCMS_PageAttributeDAO");
		$data = $dao->getByPageId($pageId);
		
		$res = array();
		
		foreach($data as $obj){
			$res[$obj->getClassName()] = $obj->getObject();
		}
		return $res;
	}
	
	public static function delete($pageId,$class){
		$dao = SOY2DAOContainer::get("SOYCMS_PageAttributeDAO");
		$dao->clearByParams($pageId,$class);
	}


	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}

/**
 * @entity SOYCMS_PageAttribute
 */
abstract class SOYCMS_PageAttributeDAO extends SOY2DAO{
	
	abstract function insert(SOYCMS_PageAttribute $bean);
	
	/**
	 * @return object
	 * @query #pageId# = :pageId AND class_name = :class
	 */
	abstract function getByParams($pageId,$class);
	
	/**
	 * @query class_name = :class AND object_data = :data
	 */
	abstract function getByValues($class,$data);
	
	/**
	 * @query page_id = :pageId AND class_name = :class
	 * @query_type delete
	 */
	abstract function clearByParams($pageId,$class);
	
	abstract function deleteByPageId($pageId);
	abstract function delete($id);
	
	abstract function get();
	
	/**
	 * @order class_name desc
	 */
	abstract function getByPageId($pageId);
	
	/**
	 * @query_type update
	 * @columns #className#,#object#
	 * @query #className# = :className
	 */
	abstract function toggleByClassName($className,$object);
		
}
?>