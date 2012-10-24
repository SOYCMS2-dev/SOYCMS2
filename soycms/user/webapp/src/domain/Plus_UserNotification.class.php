<?php
/**
 * @table plus_user_notification
 */
class Plus_UserNotification extends SOY2DAO_EntityBase{
	
	/**

	 * @param type 既読の際に連携を行います
	 * @param title
	 * @param text
	 * @param link
	 */
	public static function prepare($type,$title,$text = null,$link = null){
		$obj = new Plus_UserNotification();
		$obj->setType($type);
		$obj->setTitle($title);
		if($text)$obj->setText($text);
		if($link)$obj->setLink($link);
		return $obj;
	}
	
	/**
	 * @param string notify 通知種別
	 * @param array<Plus_User>
	 * @param mail_type
	 */
	function send($notify,$users,$type = "html"){
		$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
		$dao = $this->getDAO();	 /* @var $dao Plus_UserNotificationDAO */
		$sender = $logic->prepareSend();
		$sender->setEncoding("UTF-8");
		
		$obj = clone($this);
		
		if(!is_array($users))$users = array($users);
		
		$attributes = $this->getAttributesArray();
		foreach($users as $user){
			$addr = $user->getMailAddress();
			$body = $this->getTemplate($user->getLanguage());
			$body = $this->convertMail($body,$attributes,$user);
			
			$config = Plus_UserConfig::get($user->getId(),"notify_config",array("on_message"));
			$mobileAddr = Plus_UserConfig::get($user->getId(),"mobile_mail_address",null);
			if(!is_array($config))$config = array();
			
			//通知するようにしてる場合はメールを送る
			//設定されていない場合は標準で送信
			if(!isset($config[$notify]) || $config[$notify] == 1){
				$res = $logic->send(
					$addr,
					$this->getTitle(),
					($type == "html") ? $body : mb_convert_encoding($body,"ISO-2022-JP","UTF-8"),
					$user->getName(),
					array(
						"Content-Type" => ($type == "html") ? "text/html; charset=UTF-8" : "text/plain; charset=ISO-2022-JP"
					)
				);
			}
			
			//携帯に通知するようにしてる場合はメールを送る
			//設定されていない場合は＜送信しない＞
			if($mobileAddr && isset($config[$notify . ".mobile"]) && $config[$notify . ".mobile"] == 1){
				$body = $this->getTemplate("mobile.". $user->getLanguage());
				$body = $this->convertMail($body,$attributes,$user);
				
				$logic->send(
					$mobileAddr,
					$this->getTitle(),
					$body,
					$user->getName()
				);
				
			}
			
			try{
				//古い通知はクリアする
				$dao->updateNotifcation($user->getId(),$obj->getLink());
			}catch(Exception $e){
				var_dump($e);
				exit;
			}
			
			//save
			$obj->setId(null);
			$obj->setUserId($user->getId());
			$dao->insert($obj);
		}
		
		return $this;
	}
	
	/**
	 * @reurn string
	 */
	function convertMail($str,$array,$user = null){
		foreach($array as $key => $value){
			$str = str_replace("%{$key}%",$value,$str);
		}
		$str = str_replace("%title%",$this->title,$str);
		$str = str_replace("%submit_date%",date("Y-m-d H:i:s",$this->getSubmitDate()),$str);
		$str = preg_replace_callback("/%submit_date\|([^%]+)%/",create_function('$a','return date($a[1],'.$this->getSubmitDate().');'),$str);
		return $str;
	}
	
	/**
	 * @return array
	 */
	function getAttributesArray(){
		$array = soy2_unserialize($this->attributes);
		if(!$array)$array = array();
		return $array;
	}
	
	function setTemplate($template,$lang = "ja"){
		$this->template[$lang] = $template;
	}
	function getTemplate($lang = "ja"){
		if(!isset($this->template[$lang])){
			return $this->template["ja"];
		}
		return $this->template[$lang];
	}
	function setTemplateFilePath($path,$lang  = "ja"){
		$this->setTemplate(file_get_contents($path),$lang);
	}
	
	/* attributes */
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column notification_link
	 */
	private $link; //リンク先
	
	/**
	 * @column notification_status
	 * 0 - 未読
	 * 1 - 既読
	 */
	private $status = 0;
	
	/**
	 * @column notification_mail_status
	 * 0 - メール未送信
	 * 1 - メール送信済み
	 */
	private $mailStatus = 0;
	
	/**
	 * @column notification_type
	 */
	private $type = "plain";
	
	/**
	 * @column notitication_title
	 */
	private $title;
	
	/**
	 * @column notification_text
	 */
	private $text = "";
	
	/**
	 * @column notification_attributes
	 */
	private $attributes;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @no_persistent
	 */
	private $templates = array();
	
	function check(){
		return true;
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
	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link = $link;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getMailStatus() {
		return $this->mailStatus;
	}
	function setMailStatus($mailStatus) {
		$this->mailStatus = $mailStatus;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getText() {
		return $this->text;
	}
	function setText($text) {
		$this->text = $text;
	}
	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = soy2_serialize($attributes);
	}
	function set_attributes($attributes){
		$this->attributes = $attributes;
	}
	
	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = time();
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}

	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
}


/**
 * @entity Plus_UserNotification
 */
abstract class Plus_UserNotificationDAO extends Plus_UserDAOBase{
	
	/**
	 * @return id
	 */
	abstract function insert(Plus_UserNotification $obj);
	abstract function update(Plus_UserNotification $obj);
	abstract function delete($id);
	
	/**
	 * @sql update plus_user_notification set notification_status = 1 where user_id = :userId AND notification_link = :link
	 * @param int $userId
	 * @param int $link
	 */
	abstract function updateNotifcation($userId,$link);
	
	/**
	 * @sql update plus_user_notification set notification_status = 1 where user_id = :userId
	 * @param int $userId
	 * @param int $link
	 */
	abstract function updateByUserId($userId);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @order notification_status,id desc
	 */
	abstract function getByUserId($userId);
	
	/**
	 * @columns count(id) as notification_count
	 * @return column_notification_count
	 * @query #userId# = :userId AND notification_status = 0
	 */
	abstract function countUnreadNotifications($userId);
	
}