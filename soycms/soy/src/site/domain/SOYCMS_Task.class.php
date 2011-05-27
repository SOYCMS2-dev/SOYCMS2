<?php
/**
 * @table soycms_task
 */
class SOYCMS_Task extends SOY2DAO_EntityBase{

	/**
	 * @id
	 */
	private $id;
	
	private $owner;
	
	/**
	 * @column parent_id
	 */
	private $parent;
	
	/**
	 * @column root_id
	 */
	private $root;
	
	private $title;
	
	/**
	 * @column task_order 
	 */
	private $order = 0;
	
	private $description;
	
	/**
	 * @column task_depth
	 */
	private $depth = 0;
	
	/**
	 * @column task_start
	 */   
	private $start;
	
	/**
	 * @column task_end
	 */
	private $end;
	
	/**
	 * @column refer_url
	 */
	private $refer;
	
	/**
	 * @column task_status
	 */
	private $status = 0;
	
	private $config;
	private $_config;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column close_date
	 */
	private $closeDate;
	
	/**
	 * @no_persistent
	 */
	private $child = array();
	
  	function addChild(SOYCMS_Task $task){
  		$this->child[] = $task;
  	} 
	
	
	function check(){
		if(strlen($this->title)<1)return false;
		if(empty($this->closeDate) && $this->status == 1){
			$this->closeDate = time();
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
	function getOwner() {
		return $this->owner;
	}
	function setOwner($owner) {
		$this->owner = $owner;
	}
	function getParent() {
		return $this->parent;
	}
	function setParent($parent) {
		$this->parent = $parent;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function getDepth() {
		return $this->depth;
	}
	function setDepth($depth) {
		$this->depth = $depth;
	}
	function getStart() {
		return $this->start;
	}
	function setStart($start) {
		$this->start = $start;
	}
	function getEnd() {
		return $this->end;
	}
	function setEnd($end) {
		$this->end = $end;
	}
	function getRefer() {
		return $this->refer;
	}
	function setRefer($refer) {
		$this->refer = $refer;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	function get_config() {
		return $this->_config;
	}
	function set_config($_config) {
		$this->_config = $_config;
	}

	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = time();
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}
	function getCloseDate() {
		return $this->closeDate;
	}
	function setCloseDate($closeDate) {
		$this->closeDate = $closeDate;
	}

	function getChild() {
		return $this->child;
	}
	function setChild($child) {
		$this->child = $child;
	}

	function getRoot() {
		if(!$this->root)return $this->getId();
		return $this->root;
	}
	function setRoot($root) {
		$this->root = $root;
	}
}

/**
 * @entity SOYCMS_Task
 */
abstract class SOYCMS_TaskDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_Task $bean);
	abstract function update(SOYCMS_Task $bean);
	
	abstract function get();
	
	/**
	 * @query #status# <= :status
	 * @index id
	 */
	abstract function getByStatus($status);
	
	/**
	 * @query #status# >= 1
	 * @index id
	 */
	abstract function getFinishTask();
	
	abstract function delete($id);
	
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	
	/**
	 * @query_type update
	 * @columns #status#
	 * @query #status# = 1
	 */
	abstract function hideCompleteTask($status = 2);
	
	/**
	 * @query_type update
	 * @columns #status#,#root#,#closeDate#
	 * @query #root# = :root AND #depth# > :depth AND #status# <= :status
	 */
	abstract function syncStatus(SOYCMS_Task $bean);
	
	
	/**
	 * @return column_task_count
	 * @columns count(id) as task_count
	 * @query #status# >= 1
	 */
	abstract function countFinishTask();

	
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
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/site/task.sql");
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
		return SOYCMSConfigUtil::get("db_dir") . SOYCMS_LOGIN_SITE_ID . "_task.db";
	}
}
?>