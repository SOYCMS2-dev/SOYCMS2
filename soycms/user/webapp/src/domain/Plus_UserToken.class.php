<?php
/**
 * @table plus_user_user_token
 */
class Plus_UserToken extends SOY2DAO_EntityBase{
	
	/**
	 * 新しいTokenを発行する
	 * @return Plus_UserToken
	 */
	public static function generateToken($userId){
		//clear
		$dao = SOY2DAOFactory::create("Plus_UserTokenDAO");
		$dao->deleteByUserId($userId);
		
		$token = new Plus_UserToken();
		$token->setUserId($userId);
		if($token->check()){
			$id = $dao->insert($token);
			$token->setId($id);
		}
		
		return $token;
	}
	
	
	function __toString(){
		return $this->token;
	}
	
	function check(){
		if(!$this->limit){
			$this->setLimit(time() + 24 * 60 * 60);
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

/**
 * @entity Plus_UserToken
 */
abstract class Plus_UserTokenDAO extends Plus_UserDAOBase{
	
	abstract function get();
	
	abstract function insert(Plus_UserToken $token);
	
	/**
	 * @return object
	 */
	abstract function getByToken($token);
	
	abstract function delete($id);
	abstract function deleteByUserId($userId);
	
	/**
	 * @query #limit# < :now
	 */
	function clearOldToken(){
		$this->executeUpdateQuery($this->getQuery(),array("now" => time()));
	}
	
}
?>