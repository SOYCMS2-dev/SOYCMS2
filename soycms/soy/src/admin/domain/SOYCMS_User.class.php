<?php
/**
 * UserLevelは0がスーパーユーザ(初期管理者は0)
 * 
 * @table soycms_user
 */
class SOYCMS_User extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column user_password
	 */
	private $password;

	/**
	 * @column user_name
	 */
	private $name;
	
	/**
	 * @column user_level
	 */
	private $level = 1;

	/**
	 * @column user_mail_address
	 */
	private $mailAddress;

	/**
	 * @column user_config
	 */
	private $config;
	
	/**
	 * @column user_unique_id
	 */
	private $uKey;
	
	
	function setConfigArray($config){
		$this->config = soy2_serialize($config);
	}
	
	function getConfigArray(){
		$res = soy2_unserialize($this->config);
		if(!is_array($res)){
			return array();
		}
		
		return $res;
	}
	
	function getConfigValue($key){
		$array = $this->getConfigArray();
		return (isset($array[$key])) ? $array[$key] : null;
	}
	
	/* getter setter */

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
	function getPassword() {
		return $this->password;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(is_array($config))return $this->setConfigArray($config);
		$this->config = $config;
	}
	
	/**
	 * check valid data
	 */
	function check(){
		
		if(strlen($this->userId)<1)return false;
		if(strlen($this->name)<1){
			$this->name = $this->userId;
		}
		
		return true;	
	}

	function getLevel() {
		return $this->level;
	}
	function setLevel($level) {
		$this->level = $level;
	}
	function getUKey() {
		if(strlen($this->uKey)<1){
			$this->uKey = md5($this->userId);
		}
		return $this->uKey;
	}
	function setUKey($uKey) {
		$this->uKey = $uKey;
	}
	
	/* パスワードの暗号化関連 */

	/**
	 * パスワードが正しいかチェックする
	 *
	 * @param String 入力されたパスワード
	 * @param String 保存されているハッシュを含む文字列（algo/salt/hash）
	 */
	function checkPassword($input){
		$stored = $this->getPassword();
		list($algo, $salt, $hash) = explode("/", $stored);
		return ( $stored == self::hashString($input, $salt, $algo) );
	}

	/**
	 * 新規にパスワードをハッシュ化する
	 *
	 * @param String ハッシュ化する文字列
	 * @return String ハッシュ化された文字列（algo/salt/hash）
	 *
	 */
	function hashPassword($rawPassword){
		//saltは乱数をmd5にしたもの
		$salt = md5(mt_rand());

		if(function_exists("hash")){
			// hash関数があればSHA512で
			return self::hashString($rawPassword, $salt, "sha512");
		}else{
			// なければMD5
			return self::hashString($rawPassword, $salt, "md5");
		}
	}

	/**
	 * 文字列をハッシュ化する。algo/salt/hashの形式で返す。
	 *
	 * @param String ハッシュ化する文字列
	 * @param String ハッシュ化の際のsalt
	 * @param String ハッシュ化アルゴリズム
	 * @return String ハッシュ化された文字列（algo/salt/hash）
	 */
	private static function hashString($string, $salt, $algo){
		$algo = strtolower($algo);

		if($algo == "md5"){
			//md5はhashが使えないときための保険
			$hash = md5($salt.$string);
		}else{
			$hash = hash($algo, $salt.$string);
		}

		return "$algo/$salt/$hash";
	}
}

/**
 * @entity SOYCMS_User
 */
abstract class SOYCMS_UserDAO extends SOY2DAO{
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_User $bean);

	abstract function update(SOYCMS_User $bean);	
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByUserId($userId);
	
	/**
	 * @return object
	 */
	abstract function getByMailAddress($mailAddress);
	
	/**
	 * @index id
	 * @columns id,user_name
	 * @return columns_user_name
	 */
	abstract function map();
	
	/**
	 * @order id
	 * @index id
	 */
	abstract function get();
	
	/**
	 * @final
	 */
	function getDataSource(){
		return SOY2DAO::_getDataSource(SOYCMS_DB_DSN,SOYCMS_DB_USER,SOYCMS_DB_PASS);
	}
}

/**
 * User DataBase Entity
 * @table soycms_user_data
 */
class SOYCMS_UserData{
	
	/**
	 * @id
	 */
	private $id;

	/**
	 * @column class_name
	 */
	private $className;

	/**
	 * @column object_data
	 */
	private $object;


	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getClassName() {
		return $this->className;
	}
	function setClassName($className) {
		$this->className = $className;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}

	public static function put($class,$obj,$userId = SOYCMS_LOGIN_USER_ID){
		$data = new SOYCMS_UserData();
		$data->setClassName($class);
		$data->SetObject(serialize($obj));

		$dao = SOY2DAOFactory::create("SOYCMS_UserDataDAO");
		$dao->setName($userId);
		$dao->clear($class);
		$dao->insert($data);
	}

	public static function get($class,$onNull = false,$userId = SOYCMS_LOGIN_USER_ID){

		try{
			$dao = SOY2DAOFactory::create("SOYCMS_UserDataDAO");
			$dao->setName($userId);
			
			$data = $dao->getByClass($class);

			$res = unserialize($data->getObject());
			if($res === false)throw new Exception();

			return $res;

		}catch(Exception $e){
			
			if($onNull !== false){
				return $onNull;
			}


			throw $e;
		}
	}
	
}

/**
 * @entity SOYCMS_UserData
 */
abstract class SOYCMS_UserDataDAO extends SOY2DAO{
	
	private $name;
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_UserData $bean);
	
	/**
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
	 * @return string
	 */
	function getDBPath(){
		$path = SOYCMSConfigUtil::get("db_dir") . "user_" . $this->name . ".db";
		return $path;
	}
	
	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);
	
	/**
	 * @query_type delete
	 * @query class_name = :class
	 */
	abstract function clear($class);
	
	function init($dsn){
		$pdo = SOY2DAO::_getDataSource($dsn);
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/site/user_config.sql");
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
	 * @query id < -1
	 */
	function check(){
		$query = $this->getQuery();
		$dsn = "sqlite:" . $this->getDBPath();
		$pdo = SOY2DAO::_getDataSource($dsn);
		$pdo->exec($query);
	}
	
	function setName($name){
		$this->name = $name;
	}
	
}