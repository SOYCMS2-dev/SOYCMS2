<?php

class WrokflowManager extends SOY2LogicBase{
	
	private $status = array();
	private $workflow = array();
	
	/**
	 * ロード
	 */
	function load(){
		$array = parse_ini_file($this->getFilePath(),true);
		
		if(!isset($array["status"]) || !isset($array["workflow"])){
			$array = parse_ini_file(dirname(__FILE__) . "/nopreview/workflow.ini",true);
		}
		
		$this->status = $array["status"];
		$this->workflow = $array["workflow"];
		
	}
	
	/**
	 * 保存
	 */
	function save($value){
		$res = @parse_ini_string($value,true);
		
		if(!isset($res["status"])){
			return false;
		}
		
		if(!isset($res["workflow"])){
			return false;
		}
		
		file_put_contents($this->getFilePath(),$value);
		
		return true;
	}

	/**
	 * ワークフローの保存先
	 */
	function getFilePath(){
		$dir = SOYCMSConfigUtil::get("config_dir");
		$path = $dir . SOYCMS_LOGIN_SITE_ID . "_workflow.ini"; 
		
		if(!file_exists($path)){
			file_put_contents($path,
				file_get_contents(dirname(__FILE__) . "/default/workflow.ini")
			);
		}
		
		return $path;
	}
	
	/**
	 * ステータスのテキストを返す
	 */
	function getStatusText($status){
		return (isset($this->status[$status])) ? $this->status[$status] : $status;
	}
	
	function getStatus(){
		return $this->status;
	}
	
	/**
	 * 現在のアクションを取得
	 */
	function getActions($status,$roles){
		$res = array();
		
		$actions = $this->buildActions();
		
		foreach($actions as $key => $action){
			try{
				$action = new SOYCMS_WorkflowAction($key,$action);
			}catch(Exception $e){
				continue;
			}
			
			if($action->isValid($status,$roles)){
				$res[$key] = $action;	
			}
		}
		
		return $res;	
	}
	
	/**
	 * Actionを取得
	 */
	function getAction($key,$status,$roles){
		$action = new SOYCMS_WorkflowAction($key,$this->buildActions($key));
		
		if($action && $action->isValid($status,$roles)){
			return $action;	
		}
		
		throw new Exception("invalid action");
	}
	
	/**
	 * Actionを取得
	 * @param $_key 特定のアクション（空の場合は全て）
	 */
	function buildActions($_key = null){
		$actions = array();
		
		foreach($this->workflow as $key => $value){
			if(strpos($key,".") === false){
				
				if($_key && isset($actions[$_key]))break;
				
				$actions[$key] = array();
				$actions[$key]["workflow"] = $value;
				continue;
			}
			
			list($id,$key) = explode(".",$key);
			if(!isset($actions[$id]))continue;
			
			$actions[$id][$key] = $value;
		}
		
		if($_key){
			return $actions[$_key] ? $actions[$_key] : null;
		}
		
		return $actions;
	}
	
	/**
	 * サンプルの読み込み
	 */
	function loadSample($i){
		$sample = @file_get_contents(dirname(__FILE__) . "/sample/workflow".$i.".ini");
		return $sample;
	}
}

class SOYCMS_WorkflowAction{
	
	function SOYCMS_WorkflowAction($id,$array){
		$this->id = $id;
		$this->name = $this->_get("name",$array);
		$this->order = $this->_get("order",$array);
		$this->permissions = $this->_get("permission",$array);
		$this->operations = explode(",",$this->_get("operation",$array));
		
		$workflow = explode(" -> ",$this->_get("workflow",$array));
		
		if(count($workflow)<2){
			throw new Exception();
		}
		
		list($this->from,$this->to) = $workflow;
		
		$this->options = $array; //残り
		
	}
	
	function _get($key,$array){
		$value = @$array[$key];
		unset($array[$key]);
		return $value;
	}
	
	function isValid($status,$role){
		
		$from = $this->from;
		//check status
		$from = str_replace("*",".*",$from);
		$from = str_replace(",","|",$from);
		$from = str_replace("/","\/",$from);
		
		if(!preg_match('/^'.$from.'$/',$status)){
			return false;
		}
		
		//check role
		$permission = $this->permissions;
		$permission = str_replace("*",".*",$permission);
		$permission = str_replace(",","|",$permission);
		$permission = str_replace("/","\/",$permission);
		
		foreach($role as $_role){
			if(preg_match("/$permission/",$_role)){
				return true;
			}
		}
		
		return false;
	}
	
	/* properties */
	
	private $id;
	private $name;
	private $order = "";
	private $from;
	private $to;
	private $permissions;
	private $operations = array();
	private $options = array();
	
	function hasOperation($key){
		return (in_array($key,$this->operations));
	}
	function getOption($key){
		return (isset($this->options[$key])) ? $this->options[$key] : "";
	}
	
	/**
	 * Actionの実行
	 */
	function execute(SOYCMS_Entry $entry){
		
		//statusを変更する
		$entry->setStatus($this->to);
		
		//when close
		if($this->to == "close"){
			$entry->setPublish(0);
		}
		
		//when open
		if($this->to == "open"){
			$entry->setPublish(1);
		}
		
		if($this->hasOperation("send_comment") && isset($_POST["message"])){
			$entry->setMemo($_POST["message"]);
		}
		
		$entry->save();
		
		//メール送信
		if($this->hasOperation("send_comment") && isset($_POST["message"])){
			$title = SOYCMS_DataSets::get("mail.review.title","承認依頼が送信されました");
			$message = $_POST["message"];
			$body = 
				SOYCMS_DataSets::get("mail.review.header","メッセージが送信されました") . "\n\n" .
				"From: " . SOYCMS_LOGIN_USER_NAME . "\n" . 
				$message . "\n\n" .
				"Preview: " . soycms_create_link("entry/detail/" . $entry->getId(),true) . "?preview" . "\n\n" .
				SOYCMS_DataSets::get("mail.review.fooer","");
			
			//宛先を取得
			$toAdmins = array();
			$to = explode(",",$this->getOption("send_comment"));
			$admins = SOYCMS_Role::getAdmin();
			
			foreach($to as $_to){
				if(!isset($admins[$_to]))continue;
				$toAdmins = array_merge($toAdmins,$admins[$_to]);
			}
			
			if(count($toAdmins) > 0){
				$toAddr = array();
				SOY2::import("admin.domain.SOYCMS_User");
				$userDAO = SOY2DAOFactory::create("SOYCMS_UserDAO");
				foreach($toAdmins as $adminId){
					try{
						$user = $userDAO->getById($adminId);
						$toAddr[] = $user->getMailAddress();
					}catch(Exception $e){
						
					}
				}
				array_unique($toAddr);
				
				$mailLogic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
				$mailLogic->send($toAddr,$title,$body);
			}
		}

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
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getFrom() {
		return $this->from;
	}
	function setFrom($from) {
		$this->from = $from;
	}
	function getTo() {
		return $this->to;
	}
	function setTo($to) {
		$this->to = $to;
	}
	function getPermissions() {
		return $this->permissions;
	}
	function setPermissions($permissions) {
		$this->permissions = $permissions;
	}
	function getOperations() {
		return $this->operations;
	}
	function setOperations($operations) {
		$this->operations = $operations;
	}
	function getOptions() {
		return $this->options;
	}
	function setOptions($options) {
		$this->options = $options;
	}
}
?>