<?php
/**
 * @table soycms_history
 */
class SOYCMS_History extends SOY2DAO_EntityBase{
	
	/**
	 * @no_persistent
	 */
	public static $TYPES = array(
		"create" => "作成",
		"update" => "更新",
		"revert" => "復元",
		"delete" => "削除"
	);

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column object_type
	 */
	private $object;
	
	/**
	 * @column object_id
	 */
	private $objectId;
	
	/**
	 * @column admin_id
	 */
	private $adminId;
	
	/**
	 * @column type_text
	 */
	private $type = "update";
	private $name;
	private $content;
	private $config;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column submit_time
	 */
	private $submitTime;
	
	/**
	 * ヒストリーに追加する
	 */
	public static function addHistory($type,$_obj,$op_type = "update"){
		$obj = new SOYCMS_History();
		$obj->setObject($type);
		$obj->setObjectId($_obj->getId());
		$obj->setName($_obj->getName());
		$obj->setContent($_obj->getContent());
		$obj->setAdminId(SOYCMS_LOGIN_USER_ID);
		$obj->setType($op_type);
		
		$obj->save();
		
		//履歴から除去
		$count = SOYCMS_DataSets::get("template_history_count",20);
		SOYCMS_History::clearHistory($type,$_obj->getId(),$count);
		
		return $obj;
	}
	
	/**
	 * 掃除を行う
	 */
	public static function clearHistory($type,$objectId,$count){
		$obj = new SOYCMS_History();
		$dao = $obj->getDAO();
		$total = $dao->countHistoryByParams($type,$objectId);
		if($total < $count)return;
		
		$dao->setLimit($total - $count);
		$result = $dao->getHistories($type,$objectId);
		
		foreach($result as $obj){
			$dao->delete($obj->getId());
		}
	}
	
	function check(){
		if(!$this->objectId)return false;
		if(!$this->type)return false;
		if(!$this->object)return false;
		
		return true;
	}
	
	function __toString(){
		return date("Y-m-d H:i:s",$this->getSubmitTime());
	}
	
	function getTypeText(){
		return @self::$TYPES[$this->getType()];	
	}
	
	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = date("Ymd");
		return $this->submitDate;
	}
	function getSubmitTime() {
		if(!$this->submitTime)$this->submitTime = time();
		return $this->submitTime;
	}
	
	function getSourceObject(){
		
		switch($this->object){
			case "history":
			case "library":
			case "navigation":
			default:
				$class = "SOYCMS_" . ucwords($this->object);
				$object = call_user_func_array(array($class,"load"),array($this->objectId));
				return $object;
				break;
		}
		
	}
	
	/* getter setter */
	
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}
	function getObjectId() {
		return $this->objectId;
	}
	function setObjectId($objectId) {
		$this->objectId = $objectId;
	}
	function getAdminId() {
		return $this->adminId;
	}
	function setAdminId($adminId) {
		$this->adminId = $adminId;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}
	function setSubmitTime($submitTime) {
		$this->submitTime = $submitTime;
	}
}

/**
 * @entity SOYCMS_History
 */
abstract class SOYCMS_HistoryDAO extends SOY2DAO{
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_History $bean);
	
	/**
	 * @order id desc
	 * @group #adminId#
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function getByParams($object,$objectId);
	
	/**
	 * @return column_entry_count
	 * @columns count(id) as entry_count
	 * @group #adminId#
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function countByParams($object,$objectId);
	
	/**
	 * @columns id,#object#,#objectId#,#adminId#,#submitDate#,#submitTime#,#type#
	 * @order id desc
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function listByParams($object,$objectId);
	
	/**
	 * 古い順に取得
	 * @order id
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function getHistories($object,$objectId);
	
	/**
	 * @return column_entry_count
	 * @columns count(id) as entry_count
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function countHistoryByParams($object,$objectId);
	
	
	/**
	 * @query id < -1
	 */
	function check(){
		$query = $this->getQuery();
		$dsn = "sqlite:" . $this->getDBPath();
		$pdo = SOY2DAO::_getDataSource($dsn);
		$pdo->exec($query);
	}
	
	/**
	 * @final
	 * @override
	 */
	function getDataSource(){
		$dsn = "sqlite:" . $this->getDBPath();
		
		if(!file_exists($this->getDBPath())){
			$this->init($dsn);
		}
		
		$pdo = SOY2DAO::_getDataSource($dsn);
		
		try{
			$this->check();
		}catch(Exception $e){
			$this->init($dsn);
		}
		
		return $pdo;	
	}
	
	/**
	 * @final
	 */
	function init($dsn){
		$pdo = SOY2DAO::_getDataSource($dsn);
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/site/history.sql");
		$sqls = explode(";",$sql);

		foreach($sqls as $sql){
			try{
				if(empty($sql))continue;
				$pdo->exec($sql);
			}catch(Exception $e){
			}
		}
		 
	}
	
	/**
	 * @final
	 * @return string
	 */
	function getDBPath(){
		return SOYCMSConfigUtil::get("db_dir") . SOYCMS_LOGIN_SITE_ID . "_history.db";
	}
}
?>