<?php
/**
 * @table soycms_site_label
 */
class SOYCMS_Label extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;
	
	private $name;
	
	private $alias;
	
	/**
	 * @column label_config
	 */
	private $config = "";
	private $_config;
	
	private $directory = null;
	
	/**
	 * @column label_type
	 * 0 - root
	 * 1 - directory
	 * 2 - directory_sub
	 */
	private $type = 0;
	
	/**
	 * @column display_order
	 */
	private $order = 0;
	
	/**
	 * @no_persistent
	 */
	private $entries = array();
	
	
	function isCommon(){
		return ($this->type == 0);
	}
	
	function check(){
		if(strlen($this->alias)<1)$this->alias = $this->name;
		if(empty($this->directory)){
			$this->type = 0;
		}else{
			$this->type = 1;
			
			//サブラベル
			if(strpos($this->name,"/") !== false){
				$this->type = 2;
			}
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
	function getName($flag = false) {
		if(strlen($this->name)<1 && !$flag){
			return "[No Name]";
		}
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getAlias() {
		return $this->alias;
	}
	function setAlias($alias) {
		//先頭と末尾の「/」は取り除く
		$alias = preg_replace('/^\/|\/$/',"",$alias);
		$this->alias = $alias;
	}
	function getConfig(){
		return $this->config;
	}
	function setConfig($config) {
		if(!is_string($config)){
			$this->_config = $config;
			$config = soy2_serialize($config);
		}
		$this->config = $config;
		$this->_config = soy2_unserialize($this->config);
	}
	function getConfigObject() {
		if(!$this->_config){
			$this->_config = soy2_unserialize($this->config);
			if(!$this->_config){
				$this->_config = array(
					"icon" => "",
					"color" => "",
					"bgcolor" => "#FFFFFF",
					"color" => "#000000",
					"parentLabelId" => null
				);
				$this->config = soy2_serialize($this->_config);
			}
		}
		return $this->_config;
	}
	function setConfigObject($_config) {
		$this->_config = $_config;
		$this->config = soy2_serialize($this->_config);
	}
	
	/* 便利メソッド */
	public static function getByEntryId($entryId){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryLabelDAO");
		$list = $dao->getByEntryId($entryId);
		
		$res = array();
		foreach($list as $obj){
			$labelId = $obj->getLabelId();
			try{
				$res[$labelId] = SOY2DAO::find("SOYCMS_Label",($labelId));
			}catch(Exception $e){
				//
			}
		}
		
		return $res;
	}
	
	public static function putLabels($entryId,$labels){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryLabelDAO");
		$dao->deleteByEntryId($entryId);
		
		foreach($labels as $labelId){
			$obj = new SOYCMS_EntryLabel();
			$obj->setLabelId($labelId);
			$obj->setEntryId($entryId);
			$dao->insertImpl($obj);
		}
	}
	
	/**
	 * 記事にラベルを投入
	 */
	public static function setLabel($entryId,$labelId,$labelObj = null){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryLabelDAO");
		$obj = new SOYCMS_EntryLabel();
		$obj->setLabelId($labelId);
		$obj->setEntryId($entryId);
		$dao->insert($obj);
		
		//サブラベルの場合
		if($labelObj && $labelObj instanceof SOYCMS_Label && $labelObj->getType() == 2){
			$names = explode("/",$labelObj->getName());
			$labelDAO = SOY2DAOContainer::get("SOYCMS_LabelDAO");
			
			$name = "";
			while(count($names) > 0){
				if(strlen($name)>0)$name .= "/";
				$name .= array_shift($names);
				try{
					$label = $labelDAO->getByName($name);
					$obj->setLabelId($label->getId());
					$dao->insert($obj);
				}catch(Exception $e){
					
				}
			}
		}
		
	}
	
	/**
	 * 記事からラベルを削除
	 */
	public static function clearLabel($entryId,$labelId){
		$dao = SOY2DAOContainer::get("SOYCMS_EntryLabelDAO");
		$obj = new SOYCMS_EntryLabel();
		$obj->setLabelId($labelId);
		$obj->setEntryId($entryId);
		$dao->delete($obj);
	}

	function getEntries() {
		return $this->entries;
	}
	function setEntries($entries) {
		$this->entries = $entries;
	}

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}

	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
}

/**
 * @entity SOYCMS_Label
 */
abstract class SOYCMS_LabelDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_Label $bean);
	
	/**
	 * @sql insert into soycms_site_label(id,name) values(:id,:name)
	 */
	function insertId($id,$name){
		$this->executeUpdateQuery($this->getQuery(),$this->getBinds());
	}

	abstract function update(SOYCMS_Label $bean);
	
	/**
	 * @columns id,#order#
	 */
	abstract function updateDisplayOrder($id,$order);
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByAlias($alias);
	
	/**
	 * @return object
	 */
	abstract function getByName($name);
	
	/**
	 * @return object
	 * @query #name# = :name AND #directory# = :directory
	 */
	abstract function getByParam($name,$directory);
	
	/**
	 * @index id
	 * @order display_order, id desc
	 */
	abstract function get();
	
	/**
	 * @index id
	 * @order display_order, id
	 */
	abstract function getByType($type);
	
	/**
	 * @return object
	 * @query directory = :directory AND alias = :alias
	 */
	abstract function getByParams($directory,$alias);
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		return array($sql,$binds);
	}
	
	/**
	 * @return list
	 * @columns soycms_site_label.*
	 * @query soycms_site_entry.directory = :directory AND soycms_site_label.label_type = 1
	 * @table soycms_site_entry_label inner join soycms_site_label on (soycms_site_entry_label.label_id = soycms_site_label.id ) inner join soycms_site_entry on (soycms_site_entry_label.entry_id = soycms_site_entry.id )
	 * @index id
	 * @order soycms_site_label.display_order
	 */
	abstract function listLabelByDirectory($directory);
	
	/**
	 * @index id
	 * @query label_type = 1 AND #directory# = :directory
	 * @order #order#
	 */
	abstract function getByDirectory($directory);
	
	/**
	 * @columns count(id) as label_count
	 * @return column_label_count
	 */
	abstract function countByDirectory($directory);
	
}

/**
 * @table soycms_site_entry_label
 */
class SOYCMS_EntryLabel{
	
	/**
	 * @column entry_id
	 */
	private $entryId;
	
	/**
	 * @column label_id
	 */
	private $labelId;
	

	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}
	
	public static function countByLabelId($labelId){
		static $_dao;
		if(!$_dao)$_dao = SOY2DAOContainer::get("SOYCMS_EntryLabelDAO");
		
		if(is_numeric($labelId)){
			return $_dao->countByLabelId($labelId);
		}
		return 0;
	}
	
}
/**
 * @entity SOYCMS_EntryLabel
 */
abstract class SOYCMS_EntryLabelDAO extends SOY2DAO{
	/**
	 * @final
	 */
	function insert(SOYCMS_EntryLabel $bean){
		$this->delete($bean);
		$this->insertImpl($bean);
	}
	
	abstract function insertImpl(SOYCMS_EntryLabel $bean);
	
	/**
	 * @query #entryId# = :entryId AND #labelId# = :labelId
	 */
	abstract function delete(SOYCMS_EntryLabel $bean);
	
	abstract function deleteByEntryId($entryId);
	
	abstract function getByEntryId($entryId);
	abstract function getByLabelId($labelId);
	
	/**
	 * @columns count(entry_id) as count_entry
	 * @return column_count_entry
	 */
	abstract function countByLabelId($labelId);
	
}