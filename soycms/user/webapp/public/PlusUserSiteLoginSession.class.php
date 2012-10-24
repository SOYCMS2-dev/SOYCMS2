<?php
/**
 * @return integer user-id
 */
function plus_user_get_user_id(){
	$session = SOY2Session::get("PlusUserSiteLoginSession");
	return $session->getId();
}
/**
 * @return PlusUserSiteLoginSession
 */
function plus_user_get_session(){
	$session = SOY2Session::get("PlusUserSiteLoginSession");
	return $session;
}

class PlusUserSiteLoginSession extends SOY2Session{
	
	private $id;		//session id
	private $siteId;
	private $autoLoginId = null;
	private $name;
	private $groups = array();
	
	function login($user){
		$this->setId($user->getId());
		$this->setName($user->getName());
		$this->setGroups(explode(",",$user->getGroupIds()));
		$this->setSiteId(SOYCMS_SITE_ID);
	}
	
	function getCookieKey(){
		return strtolower(SOYCMS_SITE_ID) ."_auto_login"; 
	}
	
	function isLoggedIn(){
		return ($this->id) ? true : false;
	}
	
	function getUser(){
		static $user;
		if(!$user){
			$user = SOY2DAO::find("Plus_User",$this->id);
		}
		return $user;
	}
	
	function getProfileImage(){
		return "/img/profile.jpg";
	}
	
	function isGroup($group){
		return in_array($group,$this->groups);
	}
	
	/**
	 * 自動ログインを行う
	 */
	function autoLogin(){
		$key = $this->getCookieKey();
		if(!isset($_COOKIE[$key])){
			return;
		}
		
		try{
			$token = $_COOKIE[$key];
			$dao = SOY2DAOFactory::create("PlusUserAutoLoginSessionEntityDAO");
			$autoLogin = $dao->getByToken($token);
			
			//cast
			$this->accept($autoLogin);
			
			//UserLogin
			$user = $this->getUser();
			$this->login($user);
			
			$this->publishCookie();
			
			return true;
		}catch(Exception $e){
			
		}
		
		return false;
	}
	
	/**
	 * Cookieを発行する
	 */
	function publishCookie(){
		$token = md5(time() . rand(0,65535));
		$expire = time() + 3600 * 24 * 365;
		
		//Cookie
		setcookie($this->getCookieKey(), $token, $expire, "/");
		
		try{
			$autoLogin = SOY2DAO::find("PlusUserAutoLoginSessionEntity",$this->getAutoLoginId());
		}catch(Exception $e){
			$autoLogin = new PlusUserAutoLoginSessionEntity();
		}
		
		$autoLogin->setUserId($this->id);
		$autoLogin->setSiteId($this->getSiteId());
		$autoLogin->setToken($token);
		$autoLogin->setLimit($expire);
		$autoLogin->save();
		
		$this->setAutoLoginId($autoLogin->getId());
	}
	
	/**
	 * Cookieを削除する
	 */
	function deleteCookie(){
		//Cookie
		setcookie($this->getCookieKey(), null);
		
		try{
			$autoLogin = SOY2DAO::find("PlusUserAutoLoginSessionEntity",$this->getAutoLoginId());
			$autoLogin->delete();
		}catch(Exception $e){
			
		}
		
	}
	
	function accept(PlusUserAutoLoginSessionEntity $obj){
		$this->setAutoLoginId($obj->getId());
		$this->setId($obj->getUserId());
	}
	
	/* getter setter */
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getAutoLoginId() {
		return $this->autoLoginId;
	}
	function setAutoLoginId($autoLoginId) {
		$this->autoLoginId = $autoLoginId;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getGroups() {
		return $this->groups;
	}
	function setGroups($groups) {
		$this->groups = $groups;
	}

	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
}



/**
 * @table plus_user_auto_login
 */
class PlusUserAutoLoginSessionEntity extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column site_id
	 */
	private $siteId;
	
	/**
	 * @column session_token
	 */
	private $token;
	
	/**
	 * @column time_limit
	 */
	private $limit;
	
	function check(){
		
		$this->getDAO()->check();
		
		return true;
	}
	
	/* setter getter */
	
	

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

	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
}



/**
 * @entity PlusUserAutoLoginSessionEntity
 */
abstract class PlusUserAutoLoginSessionEntityDAO extends SOY2DAO{
	
	/**
	 * @query id < 0
	 */
	function check(){
		try{
			$this->executeQuery($this->getQuery());
		}catch(Exception $e){
			$sql = "create table plus_user_auto_login(" .
						"id integer primary key," .
						"user_id integer not null," .
						"site_id VARCHAR(128) not null," .
						"session_token CHAR(32) NOT NULL," .
						"time_limit integer" .
					");";
			$this->executeUpdateQuery($sql);
		}
	}
	
	/**
	 * @return id
	 */
	abstract function insert(PlusUserAutoLoginSessionEntity $obj);
	
	abstract function update(PlusUserAutoLoginSessionEntity $obj);
	
	abstract function delete($id);
	
	/**
	 * @query #limit# < :time
	 */
	abstract function deleteByTime($time);
	
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByToken($token);
	
	/**
	 * @final
	 */
	function getDataSource(){
		$path = "sqlite:" . SOYCMSConfigUtil::get("db_dir") . SOYCMS_SITE_ID . "_user_session.db";
		return SOY2DAO::_getDataSource($path,"","");
	}
	
	function getMasterDataSource(){
		return $this->getDataSource();
	}
	
}

?>