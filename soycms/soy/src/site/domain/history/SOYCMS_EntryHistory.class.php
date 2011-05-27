<?php
/**
 * @table soycms_entry_history
 */
class SOYCMS_EntryHistory extends SOY2DAO_EntityBase{
	
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
	 * @column entry_id
	 */
	private $entryId;
	
	/**
	 * @column admin_id
	 */
	private $adminId;
	
	/**
	 * @column type_text
	 */
	private $type = "update";
	private $comment;
	private $title;
	private $sections; //serialized section data
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column submit_time
	 */
	private $submitTime;
	
	/**
	 * @column entry_status
	 */
	private $status;
	
	/**
	 * ヒストリーに追加する
	 */
	public static function addHistory($entry,$type,$comment = ""){
		if(!$comment)$comment = $entry->getMemo();
		
		$obj = new SOYCMS_EntryHistory();
		$obj->setEntryId($entry->getId());
		$obj->setComment($comment);
		$obj->setType($type);
		$obj->setAdminId(SOYCMS_LOGIN_USER_ID);
		$obj->setStatus($entry->getStatus());
		
		$obj->setTitle($entry->getTitleSection());
		$obj->setSections($entry->getSections());
		
		$obj->save();
	}
	
	/**
	 * 掃除を行う
	 */
	public static function clearHistory($entryId,$count){
		$obj = new SOYCMS_EntryHistory();
		$dao = $obj->getDAO();
		$total = $dao->countHistoryByEntryId($entryId);
		if($total < $count)return;
		
		$dao->setLimit($total - $count);
		$result = $dao->getHistories($entryId);
		
		foreach($result as $obj){
			$dao->delete($obj->getId());
		}
	}
	
	function merge(SOYCMS_Entry $entry){
		$entry->setTitleSection($this->getTitle());
		$entry->setSections($this->getSections());
	}
	
	function check(){
		if(!$this->entryId)return false;
		if(!$this->type)return false;
		
		return true;
	}
	
	function __toString(){
		return date("Y-m-d H:i:s",$this->getSubmitTime());
	}
	
	function getTypeText(){
 	  	return @self::$TYPES[$this->getType()];	
	}
	
	/* getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getComment() {
		return $this->comment;
	}
	function setComment($comment) {
		$this->comment = $comment;
	}
	function getSections() {
		return $this->sections;
	}
	function setSections($sections) {
		$this->sections = $sections;
	}
	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = date("Ymd");;
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}

	function getAdminId() {
		return $this->adminId;
	}
	function setAdminId($adminId) {
		$this->adminId = $adminId;
	}

	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}

	function getSubmitTime() {
		if(!$this->submitTime)$this->submitTime = time();
		return $this->submitTime;
	}
	function setSubmitTime($submitTime) {
		$this->submitTime = $submitTime;
	}

	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
}

/**
 * @entity SOYCMS_EntryHistory
 */
abstract class SOYCMS_EntryHistoryDAO extends SOY2DAO{
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_EntryHistory $bean);
	
	/**
	 * @order id desc
	 * @group #adminId#
	 * @query #entryId# = :entryId
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @return column_entry_count
	 * @columns count(id) as entry_count
	 * @group #adminId#
	 * @query #entryId# = :entryId
	 */
	abstract function countByEntryId($entryId);
	
	/**
	 * @columns id,#entryId#,#adminId#,#submitDate#,#submitTime#,#comment#,#status#,#type#
	 * @order id desc
	 * @query #entryId# = :entryId
	 */
	abstract function listByEntryId($entryId);
	
	/**
	 * 古い順に取得
	 * @order id
	 * @query #entryId# = :entryId
	 */
	abstract function getHistories($entryId);
	
	/**
	 * @return column_entry_count
	 * @columns count(id) as entry_count
	 */
	abstract function countHistoryByEntryId($entryId);
	
	
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
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/site/entryhistory.sql");
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
		return SOYCMSConfigUtil::get("db_dir") . SOYCMS_LOGIN_SITE_ID . "_entry_history.db";
	}
}
?>