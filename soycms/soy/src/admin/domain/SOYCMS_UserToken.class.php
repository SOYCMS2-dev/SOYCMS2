<?php
/**
 * @table soycms_user_token
 */
class SOYCMS_UserToken extends SOY2DAO_EntityBase{
	
	/**
	 * 新しいTokenを発行する
	 * @return SOYCMS_UserToken
	 */
	public static function generateToken($userId){
		$token = new SOYCMS_UserToken();
		$token->setUserId($userId);
		$token->save();
		return $token;
	}
	
	
	function __toString(){
		return $this->token;
	}
	
	function check(){
		if(!$this->limit){
			$this->setLimit(time() + 3 * 60 * 60);
		}
		if(!$this->userId)return false;
		
		return true;
	}
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	private $token;
	
	/**
	 * @column time_limit
	 */
	private $limit;
	
	private $config;
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getToken() {
		if(strlen($this->token)<1){
			$crypt = md5(time() . "_" . $this->userId);
	   		$query = (base64_encode(substr($crypt,strlen($crypt)-15)));
	   		return $query;
		}
		
		return $this->token;
	}
	function setToken($token) {
		$this->token = $token;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}

abstract class SOYCMS_UserTokenDAO extends SOY2DAO{
	
	abstract function get();
	
	abstract function insert(SOYCMS_UserToken $token);
	
	/**
	 * @return object
	 */
	abstract function getByToken($token);
	
	abstract function delete($id);
	
	/**
	 * @query #limit# < :now
	 */
	function clearOldToken(){
		$this->executeUpdateQuery($this->getQuery(),array("now" => time()));
	}
	
	/* 初期化関連のタグ */
	
	function init($dsn){
		$pdo = SOY2DAO::_getDataSource($dsn);
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/admin/user_token.sql");
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
	 * @override
	 */
	function &getDataSource(){
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
	 * @return string
	 */
	function getDBPath(){
		$path = SOYCMSConfigUtil::get("db_dir") . "token.db";
		return $path;
	}
	
	/**
	 * @query id < -1
	 */
	function check(){
		$query = $this->getQuery();
		$dsn = "sqlite:" . $this->getDBPath();
		$pdo = SOY2DAO::_getDataSource($dsn);
		$pdo->exec($query);
	}
	
}
?>