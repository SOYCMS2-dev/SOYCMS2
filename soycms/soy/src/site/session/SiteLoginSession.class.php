<?php
/**
 * サイトにログインするセッション
 */
class SiteLoginSession extends SOY2Session{
	
	private $id;
	private $userId;
	private $userName;
	private $siteId;
	private $siteName;
	private $siteURL;
	private $siteRootURL;
	private $roles = array();
	private $groups = array();
	private $theme = "gray";
	private $config = array();
	
	function wakeup(){
		$this->configure();
	}
	
	function applyConfig(){
		SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);
	}
	
	/**
	 * 設定ファイルを読み込む
	 */
	private function configure(){
		if(isset($_GET["init_site"])){
			return false;
		}
		
		if(!$this->siteId){
			$this->destroy();
			return false;
		}
		
		if(isset($_GET["login"]) && strlen($_GET["login"])<1){
			return false;
		}
		
		SOYCMSConfigUtil::loadConfig("site/" . $this->siteId . ".conf.php");
		
		if(!defined("SOYCMS_SITE_DB_DSN")){
			$this->destroy();
			return false;
		}
		
		SOY2DAOConfig::Dsn(SOYCMS_SITE_DB_DSN);
		SOY2DAOConfig::user(SOYCMS_SITE_DB_USER);
		SOY2DAOConfig::password(SOYCMS_SITE_DB_PASS);
		
		if(!defined("SOYCMS_LOGIN_SITE_ID") && !defined("SOYCMS_SITE_URL")){
			$root = "";//SOY2PageController::createRelativeLink("/",true);
			define("SOYCMS_LOGIN_SITE_ID",	$this->siteId);
			define("SOYCMS_SITE_URL",		$this->siteURL);
			define("SOYCMS_SITE_ROOT_URL",	$this->siteRootURL);
		}
		
		if(!defined("SOYCMS_SITE_ID")){
			define("SOYCMS_SITE_ID",	$this->siteId);
		}
		
		if(!class_exists("SOYCMS_DataSets")){
			SOY2::imports("site.domain.*");
			SOY2::imports("site.domain.group.*");
		}
		
		//timezone
		$default = @date_default_timezone_get();
		if(!$default)$default = "Asia/Tokyo";
		$timezone = SOYCMS_DataSets::get("timezone",@date_default_timezone_get());
		if($timezone != $default){
			date_default_timezone_set($timezone);
		}
	}
	
	/**
	 * ログイン実行
	 */
	function login($site,$userId,$onlyTest = false){
		$this->setId($site->getId());
		$this->setSiteName($site->getName());
		$this->setSiteId($site->getSiteId());
		$this->setSiteURL($site->getUrl());
		$this->setSiteRootURL($site->getUrl());
		$this->setUserId($userId);
		
		//ドメインルート時
		if(SOYCMS_CommonConfig::get("DomainRootSite",null) == $site->getSiteId()){
			$this->setSiteRootURL(SOY2FancyURIController::createRelativeLink("/",true));
		}
		
		try{
		
			//Role
			$this->configure();
			$roles = SOY2DAO::find("SOYCMS_Role",array("adminId"=>$userId));
			$this->roles = array();
			
			foreach($roles as $role){
				$this->roles[] = $role->getRole();
			}
			
			//Session check
			$userLoginSession = SOY2Session::get("base.session.UserLoginSession");
			
			if($userLoginSession->isSuperUser() && !in_array("super",$this->roles)){
				$this->roles[] = "super";
				$this->roles[] = "editor";
				$this->roles[] = "designer";
				$this->roles[] = "publisher";
			}
			
			if(empty($this->roles)){
				$this->destroy();
				return false;
			}
		
		}catch(Exception $e){
			$this->roles[] = "super";
		}
		
		//試すだけの場合
		if($onlyTest){
			$res = (count($this->roles) > 0);
			$this->destroy();
			return $res;
		}
		
		try {
			//Group
			$groups = SOY2DAO::find("SOYCMS_AdminGroup",array("adminId" => $userId));
			$this->groups = $groups;
		}catch( Exception $e ) {
			
		}
		
		//get config
		SOY2::import("site.domain.SOYCMS_DataSets");
		$config = SOYCMS_DataSets::get("config_custom",array());
		if(isset($config["cp_theme"]))$this->setTheme($config["cp_theme"]);
		$this->setConfig($config);
		return true;
	}
	
	function hasRole($role){
		return in_array($role,$this->roles);
	}
	
	/**
	 * サイト管理系のroleがあるかどうか
	 */
	function hasSiteRole(){
		$roles = array(
			"super",
			"designer",
			"editor",
			"publisher",
			"author"
		);
		
		foreach($roles as $role){
			if(in_array($role,$this->roles))return true;
		}
		
		return false;
	}
	
	/**
	 * @return boolean
	 */
	function checkPermission($pageId,$writable = false){
		
		//管理者権限がある場合はチェックしない
		if($this->hasRole("super")){
			return true;
		}
		
		try{
			$permission = SOY2DAO::find("SOYCMS_GroupPermission",array("pageId" => $pageId));
			
			//パーミッションが設定されていない場合は全てOKとする
			if(empty($permission)){
				return true;
			}
			
			//全てのグループをチェックする
			$groups = $this->groups;
			
			foreach($groups as $group){
				if(!isset($permission[$group]))continue;
				
				//書き込み権限があればOK
				if($writable && $permission[$group]->isWritable()){
					return true;
				}
				
				//読み込み権限があればOK
				if(!$writable && $permission[$group]->isReadable()){
					return true;
				}
			}
			
			
		}catch(Exception $e){
			return true;
		}
		
		return false;
	}
	
	/* getter setter */
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}

	function getSiteURL() {
		return $this->siteURL;
	}
	function setSiteURL($siteURL) {
		$this->siteURL = $siteURL;
	}

	function getSiteName() {
		return $this->siteName;
	}
	function setSiteName($siteName) {
		$this->siteName = $siteName;
	}

	function getUserName() {
		if(!$this->userName)$this->userName = "root";
		return $this->userName;
	}
	function setUserName($userName) {
		$this->userName = $userName;
	}

	function getRoles() {
		return $this->roles;
	}
	
	function setRoles($roles) {
		$this->roles = $roles;
	}

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}

	function getTheme() {
		return $this->theme;
	}
	function setTheme($theme) {
		$this->theme = $theme;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}


	function getGroups() {
		return $this->groups;
	}
	function setGroups($groups) {
		$this->groups = $groups;
	}

	function getSiteRootURL() {
		return $this->siteRootURL;
	}
	function setSiteRootURL($siteRootURL) {
		$this->siteRootURL = $siteRootURL;
	}
}

/**
 * @table soycms_site_user_activity
 */
class SOYCMS_SiteUserActivity extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column user_name
	 */
	private $userName;
	
	private $token;	/* 画面ID */
	
	private $uri;
	
	/**
	 * @column http_query
	 */
	private $query;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	function check(){
		$this->submitData = true;
		$this->userId = SOYCMS_LOGIN_USER_ID;
		$this->userName = SOYCMS_LOGIN_USER_NAME;
		$this->token = $this->userId . $this->token;
		
		$this->getDAO()->check();
		
		return true;
	}

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
		$this->token = crc32($token);
	}
	function getUri() {
		return $this->uri;
	}
	function setUri($uri) {
		$this->uri = $uri;
	}
	function getQuery() {
		return $this->query;
	}
	function setQuery($query) {
		$this->query = $query;
	}
	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = time();
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}

	function getUserName() {
		return $this->userName;
	}
	function setUserName($userName) {
		$this->userName = $userName;
	}
}
/**
 * @entity SOYCMS_SiteUserActivity
 */
abstract class SOYCMS_SiteUserActivityDAO extends SOY2DAO{
	
	/**
	 * @query id < 0
	 */
	function check(){
		try{
			$this->executeQuery($this->getQuery());
		}catch(Exception $e){
			$sql = "create table soycms_site_user_activity(" .
						"id integer primary key," .
						"user_id integer not null," .
						"user_name varchar," .
						"uri VARHCAR not null," .
						"token VARHCAR not null," .
						"http_query VARCHAR," .
						"submit_date integer" .
					");";
			$this->executeUpdateQuery($sql);
		}
	}
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_SiteUserActivity $obj);
	
	abstract function update(SOYCMS_SiteUserActivity $obj);
	
	abstract function delete($id);
	
	/**
	 * @query #submitDate# < :time
	 */
	abstract function deleteByTime($time);
	
	abstract function get();
	
	/**
	 * @query uri = :uri AND token <> :token
	 * @group token
	 */
	abstract function searchOtherUser($uri,$token);
	
	/**
	 * @query uri = :uri
	 * @group token
	 */
	abstract function getByUri($uri);
	
	/**
	 * @final
	 * AutoLoginSessionと同じ
	 */
	function &getDataSource(){
		if(!defined("SOYCMS_LOGIN_SITE_ID"))return null;
		$path = "sqlite:" . SOYCMSConfigUtil::get("db_dir") . SOYCMS_LOGIN_SITE_ID . "_session.db";
		return SOY2DAO::_getDataSource($path,"","");
	}
}

?>