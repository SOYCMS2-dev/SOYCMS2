<?php

class PlusUserConfig {

	/**
	 * @return PlusUserConfig
	 */
	public static function getConfig(){
		static $obj = null;
		
		if(!$obj){
			$obj = new PlusUserConfig();
			
			//設定ファイルを上書き可能
			$res = SOYCMSConfigUtil::get("user_conf");
			if($res){
				soy2_require($res);
			}else{
				SOYCMSConfigUtil::loadConfig("plus_user.conf.php");
			}
			
			
			
			if(!defined("PLUS_USER_HOST")){
				$siteDsn = SOYCMS_SITE_DB_DSN;
				$host = "localhost";
				$db = "soycms_plus_user";
				$user = "root";
				$pass = "";
				if(preg_match('/^mysql:host=(.*)?;dbname=(.*)/',$siteDsn,$tmp)){
					$host = $tmp[1];
					$db = $tmp[2];
					$user = SOYCMS_SITE_DB_USER;
					$pass = SOYCMS_SITE_DB_PASS;
				}
				
				define("PLUS_USER_HOST",$host);
				define("PLUS_USER_DB",$db);
				define("PLUS_USER_USER",$user);
				define("PLUS_USER_PASS",$pass);
			}
			
			if(defined("PLUS_USER_MEMBER_PAGE_URL")){
				$obj->setMemberPageUrl(@PLUS_USER_MEMBER_PAGE_URL);
				$obj->setMemberPageId(@PLUS_USER_MEMBER_PAGE_ID);
			}
			
			$database = array(
				"host" => PLUS_USER_HOST,
				"db" => PLUS_USER_DB,
				"user" => PLUS_USER_USER,
				"password" => PLUS_USER_PASS
			);
			
			$obj->setDatabaseConfig($database);
			
			//options
			$obj->setOptions(SOYCMS_DataSets::get("plus.user.config",array(
				"not_login_forward_uri" => "login",
				"logout_forward_uri" => "top"
			)));
		}
		
		return $obj;
	}
	
	public static function saveConfig($config){
		
		$res = SOYCMSConfigUtil::get("user_conf");
		$filepath = SOYCMSConfigUtil::get("config_dir") . "plus_user.conf.php";
		if($res)$filepath = $res;
		
		
		$file = array();
		$file[] = "<?php";
		
		//DB周り
		$database = $config->getDatabaseConfig();
		$file[] = 'define("SOYCMS_SITE_DB_DSN", "");';
		$file[] = 'define("PLUS_USER_HOST", "'.$database["host"].'");';
		$file[] = 'define("PLUS_USER_DB", "'.$database["db"].'");';
		$file[] = 'define("PLUS_USER_USER", "'.$database["user"].'");';
		$file[] = 'define("PLUS_USER_PASS", "'.$database["password"].'");';
		
		//Myページ周り
		$file[] = 'define("PLUS_USER_MEMBER_PAGE_URL", "'.$config->getMemberPageUrl().'");';
		$file[] = 'define("PLUS_USER_MEMBER_PAGE_ID", "'.$config->getMemberPageId().'");';
		
		file_put_contents($filepath,implode("\n",$file));
		
		//option
		SOYCMS_DataSets::put("plus.user.config",$config->getOptions());
	}
	
	/* functions */
	
	private function PlusUserConfig(){
		//go singleton
	}
	
	function getConnection(){
		$database = $this->getDatabaseConfig();
		
		try{
			$pdo = new PDO(
				$database["dsn"],
				$database["user"],
				$database["password"],
				array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
			);
		}catch(Exception $e){
			throw $e;
		}
		
		return $pdo;
	}
	
	function checkConnection(){
		
		try{
			$pdo = $this->getConnection();
			
		}catch(Exception $e){
			return false;
		}
		
		
		//テーブル作成のテスト
		try{
			$res = $pdo->exec("create table plus_user_test(id integer)");
			$res = $pdo->exec("drop table plus_user_test");
			
			return true;
		}catch(Exception $e){
			return false;
		}
		
		
		return false;
	}
	
	function getModuleMapping(){
		if(@$this->mappings)return $this->mappings;
		
		$this->mappings = SOYCMS_DataSets::get("plus.user.module.mapping",array(
			"plus_user_connector.top" => array(
				"active" => true,
				"url" => "top"
			),
			"plus_user_connector.profile" => array(
				"active" => true,
				"url" => "profile"
			),
			"plus_user_connector.login" => array(
				"active" => true,
				"url" => "login",
				"login" => false
			),
			"plus_user_connector.logout" => array(
				"active" => true,
				"url" => "logout"
			),
			"plus_user_connector.register" => array(
				"active" => true,
				"url" => "register",
				"login" => false
			)
		));
		
		return $this->mappings;
	}
	
	function getOption($key, $def = null){
		if(isset($this->options[$key]))return $this->options[$key];
		return $def;
	}
	
	function setModuleMapping($mapping){
		SOYCMS_DataSets::put("plus.user.module.mapping",$mapping);
	}
	
	function getModulePageUrl($moduleId,$suffix = null,$query = array()){
		$mappings = $this->getModuleMapping();
		$mapping = (isset($mappings[$moduleId])) ? $mappings[$moduleId] : array();
		
		if(@$mapping["login"] == 1){
			$url = soycms_get_page_url(
				$this->getMemberPageUrl(),
				$this->getModulePageUri($moduleId,$suffix,$query)
			);
		}else{
			$url = soycms_get_page_url(
				$this->getModulePageUri($moduleId,$suffix,$query)
			);
		}
		return $url;
	}
	
	
	function getModulePageUri($moduleId,$suffix = null,$query = array()){
		$mappings = $this->getModuleMapping();
		if(!isset($mappings[$moduleId])){
			return "";
		}
		$url = $mappings[$moduleId]["url"];
		
		if($suffix){
			$url = soycms_union_uri($url,$suffix);
		}
		
		if($query){
			$url .= "?" . http_build_query($query);
		}
		
		return $url;
	}
	
	function isModuleActive($moduleId){
		$mappings = $this->getModuleMapping();
		if(!isset($mappings[$moduleId]))return false;
		return $mappings[$moduleId]["active"];
	}
	
	private $memberPageUrl = "mypage";
	private $memberPageId = null;
	private $databaseConfig = array();
	private $options = array();
	
	/* getter setter */

	function getMemberPageUrl() {
		return $this->memberPageUrl;
	}
	function setMemberPageUrl($memberPageUrl) {
		$this->memberPageUrl = $memberPageUrl;
	}
	function getMemberPageId() {
		return $this->memberPageId;
	}
	function setMemberPageId($memberPageId) {
		$this->memberPageId = $memberPageId;
	}

	function getDatabaseConfig() {
		return $this->databaseConfig;
	}
	function setDatabaseConfig($databseConfig) {
		$databseConfig["dsn"] = "mysql:host=" . $databseConfig["host"] . ";dbname=" . $databseConfig["db"];
		$this->databaseConfig = $databseConfig;
	}


	function getOptions() {
		return $this->options;
	}
	function setOptions($options) {
		$this->options = $options;
	}
}
?>