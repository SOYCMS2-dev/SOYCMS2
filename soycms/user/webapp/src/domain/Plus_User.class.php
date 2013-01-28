<?php
/**
 * @table plus_user_user
 */
class Plus_User extends SOY2DAO_EntityBase{
	
	function check(){
		if(strlen($this->loginId)<4)return false;
		if(!$this->updateDate)$this->createDate = time();
		$this->updateDate = time();
		
		//profile url
		if(preg_match("/https?:\/\//",$this->profileUrl)){
			$res = parse_url($this->profileUrl);
			$this->profileUrl = $res["path"];
		}
		
		return true;
	}
	
	function saveProfile($flag = false){
		Plus_UserProfile::saveProfile($this->getId(),$this->getProfile(),$flag);
	}
	
	function isGroup($group){
		$groups = explode(",",$this->groupIds);
		return in_array($group,$groups);
	}
	
	function uploadImage($filepath,$width = null){
		$path = "user/icon-" . $this->getId() . ".jpg";
		$newpath = SOYCMS_SITE_DIRECTORY . $path;
		if(!file_exists(dirname($newpath))){
			soy2_mkdir(dirname($newpath));
		}
		
		$newurl = soycms_union_uri(SOYCMS_SITE_URL,$path);
		if(move_uploaded_file($filepath,$newpath)){
			$this->setProfileImageUrl($newurl);
		}
		
		if($width){
			soy2_resizeimage($newpath, $newpath, $width);
		}
	}
	
	/**
	 * Plus_UserConfig::get()のラッパー
	 * @return val
	 * @param unknown_type $key
	 * @param unknown_type $def
	 */
	function getConfigValue($key,$def = null){
		return Plus_UserConfig::get($this->id, $key, $def);
	}
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * 表示名
	 */
	private $name;
	
	/**
	 * @column login_id
	 * ログイン用のID
	 */
	private $loginId;
	
	/**
	 * パスワード
	 */
	private $password;
	
	/**
	 * @column mail_address
	 */
	private $mailAddress;
	
	/**
	 * @column user_status
	 * -1 - 退会済み
	 * 0 -  仮登録
	 * 1 - 登録
	 */
	private $status = 0;
	
	
	/**
	 * @column configure
	 */
	private $config;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	/**
	 * @no_persistent
	 */
	private $profile = null;
	
	/**
	 * @column group_ids
	 */
	private $groupIds;
	
	/**
	 * @column profile_url
	 */
	private $profileUrl;
	
	/**
	 * @column profile_image_url
	 */
	private $profileImageUrl;
	
	/**
	 * @column language
	 */
	private $language = null;
	
	/**
	 * @no_persistent
	 */
	private $_attributes = array();
	
	/**
	 * 有効なユーザかどうか判定
	 */
	function isActive(){
		return ($this->status > 0);
	}
	
	function setAttribute($key,$val){
		$this->_attributes[$key] = $val;
		return $this;
	}
	function getAttribute($key){
		return (isset($this->_attributes[$key])) ? $this->_attributes[$key] : null;
	}
	
	/* getter setter */
	

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getLoginId() {
		return $this->loginId;
	}
	function setLoginId($loginId) {
		$this->loginId = $loginId;
	}
	function getPassword() {
		return $this->password;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getCreateDate() {
		if(!$this->createDate)$this->createDate = time();
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getUpdateDate() {
		if(!$this->updateDate)$this->updateDate = time();
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	
	/**
	 * グループ
	 */
	function getGroupIds(){
		return $this->groupIds;
	}
	
	function setGroupIds($groupIds){
		if(is_array($groupIds))$groupIds = implode(",",$groupIds);
		$this->groupIds = $groupIds;
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
		@list($algo, $salt, $hash) = explode("/", $stored);
		
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

	/**
	 * @return array
	 */
	function getProfile() {
		if(!is_array($this->profile))return array();
		return $this->profile;
		
	}
	function setProfile($profile) {
		$this->profile = $profile;
	}

	function setGroups($groups) {
		$this->groups = $groups;
	}

	function getProfileUrl() {
		return $this->profileUrl;
	}
	function setProfileUrl($profileUrl) {
		$this->profileUrl = $profileUrl;
	}

	function getProfileImageUrl() {
		if(empty($this->profileImageUrl))return soycms_union_uri(SOYCMS_SITE_URL,"themes/default/img/dummy.gif");
		return $this->profileImageUrl;
	}
	function setProfileImageUrl($profileImageUrl) {
		$this->profileImageUrl = $profileImageUrl;
	}

	function getLanguage() {
		return $this->language;
	}
	function setLanguage($language) {
		$this->language = $language;
	}
}

/**
 * @entity Plus_User
 */
abstract class Plus_UserDAO extends Plus_UserDAOBase{
	
	/**
	 * @return id
	 */
	abstract function insert(Plus_User $obj);
	abstract function update(Plus_User $obj);
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByLoginId($loginId);
	
	/**
	 * @return object
	 */
	abstract function getByMailAddress($mailAddress);
	
	abstract function get();
	
	/**
	 * @query user_status > 0
	 */
	abstract function getActiveUsers();
	
}
