<?php
class AutoLoginSession extends SOY2Session{
	
	private $id;
	private $token;
	private $userId;
	private $siteId = 0;
	private $limit;
	
	/**
	 * 自動ログインを行う
	 */
	function autoLogin(){
		
		if(!isset($_COOKIE["soycms_auto_login"])){
			return;
		}
		
		try{
			$token = $_COOKIE["soycms_auto_login"];
			$dao = SOY2DAOFactory::create("AutoLoginSessionEntityDAO");
			$autoLogin = $dao->getByToken($token);
			
			//cast
			SOY2::cast($this,$autoLogin);
			
			//UserLogin
			$user = SOY2DAO::find("SOYCMS_User",$this->getUserId());
			$session = SOY2Session::get("base.session.UserLoginSession");
			$session->doLogin($user);
			
			//SiteLogin
			$site = SOY2DAO::find("SOYCMS_Site",$this->getSiteId());
			$session = SOY2Session::get("site.session.SiteLoginSession");
			$session->login($site,$this->getUserId());
			
			$this->publishCookie();
			$this->save();
			
			//SiteUserLoginSession
			$userLoginSession = SOY2Session::get("site.session.SiteUserLoginSession");
			$userLoginSession->setSiteId($site->getSiteId());
			$userLoginSession->setSoycmsRoot(SOY2FancyURIController::createRelativeLink("../",true));
			
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
		setcookie("soycms_auto_login", $token, $expire, "/");
		
		$this->token = $token;
		$this->limit = $expire;
		
	}
		
	/**
	 * Cookieを削除する
	 */
	function deleteCookie(){
		//Cookie
		setcookie("soycms_auto_login", null);
		
		try{
			$autoLogin = SOY2DAO::find("AutoLoginSessionEntity",$this->getId());
			$autoLogin->delete();
		}catch(Exception $e){
			
		}
		
	}
	
	/**
	 * 保存
	 */
	function save(){
		try{
			$autoLogin = SOY2DAO::find("AutoLoginSessionEntity",$this->getId());
		}catch(Exception $e){
			$autoLogin = new AutoLoginSessionEntity();
		}
		
		SOY2::cast($autoLogin,$this);
		$id = $autoLogin->save();
		$this->setId($autoLogin->getId());
	}
	
	/* getter setter */
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getToken() {
		return $this->token;
	}
	function setToken($token) {
		$this->token = $token;
	}
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}
}

/**
 * @table soycms_auto_login
 */
class AutoLoginSessionEntity extends SOY2DAO_EntityBase{
	
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
 * @entity AutoLoginSessionEntity
 */
abstract class AutoLoginSessionEntityDAO extends SOY2DAO{
	
	/**
	 * @query id < 0
	 */
	function check(){
		try{
			$this->executeQuery($this->getQuery());
		}catch(Exception $e){
			$sql = "create table soycms_auto_login(" .
						"id integer primary key," .
						"user_id integer not null," .
						"site_id integer not null," .
						"session_token CHAR(32) NOT NULL," .
						"time_limit integer" .
					");";
			$this->executeUpdateQuery($sql);
		}
	}
	
	/**
	 * @return id
	 */
	abstract function insert(AutoLoginSessionEntity $obj);
	
	abstract function update(AutoLoginSessionEntity $obj);
	
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
	function &getDataSource(){
		$path = "sqlite:" . SOYCMSConfigUtil::get("db_dir") . "session.db";
		return SOY2DAO::_getDataSource($path,"","");
	}
	
}


?>