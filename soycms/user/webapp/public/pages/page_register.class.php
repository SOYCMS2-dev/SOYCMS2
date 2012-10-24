<?php

/**
 * グループにユーザを登録
 */
class page_register extends PlusUserWebPageBase{
	
	private $mode = "mail";
	private $modes = array(
		"mail",
		"code",
		"code_error",
		"input",
		"confirm",
		"complete"
	);
	private $user = null;

	private $groupId = null;
	private $group = null;
	private $errors = array();
	private $profile = array();
	
	function getModuleUrl($option){
		return $this->getConfig()->getModulePageUri("plus_user_connector.register." . $this->groupId,$option);
	}
	
	function doPost(){
		
		$config = $this->getConfig();
		
		//completeから戻った場合
		if($this->mode == "complete" && isset($_SESSION["plus_user_complete_user_id"])){
			$userId = $_SESSION["plus_user_complete_user_id"];
			$user = SOY2DAO::find("Plus_User",$userId);
			
			/* @var $session PlusUserSiteLoginSession */
			$session = SOY2Session::get("PlusUserSiteLoginSession");
			$session->login($user);
			
			PlusUserApplicationHelper::getController()->jumpToTop();
		}
		
		if($this->mode == "mail" && isset($_POST["User"])){
			$this->user->setId(null);
			SOY2::cast($this->user,$_POST["User"]);
			try{
				if($this->checkMailAddress($this->user)){
					
					//保存
					$this->user->setLoginId($this->user->getMailAddress());
					$this->user->setStatus(0);	//仮登録
					$this->user->setPassword("regiser");
					$this->user->save();
					
					//Profileのクリア
					Plus_UserProfile::saveProfile($this->id, array());
					
					//Tokenの発行
					$token = Plus_UserToken::generateToken($this->user->getId());
					
					//確認メールの送信
					$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
					
					$title = SOYCMS_DataSets::get("plus.user.mail.".$this->group->getGroupId().".register.title","");
					$url = soycms_get_page_url($this->getModuleUrl("code"));
					$body = SOYCMS_DataSets::get("plus.user.mail.".$this->group->getGroupId().".register.body","");
					$body = str_replace("#CODE#", $token->getToken(),$body);
					$body = str_replace("#CODE_URL#", $url . "?code=" . $token->getToken(),$body);
					
					try{
						$logic->send(
							$this->user->getMailAddress(),
							$title,
							$body
						);
					}catch(Exception $e){
						//var_dump($e);
					}
					
					//セッションに投入
					$_SESSION["plus_user_register"] = SOY2::cast("object",$this->user);
					
					$url = $this->getModuleUrl("code");
					
					//for debug
					//$url = $this->getModuleUrl("code") . "?code=" . $token->getToken();
					SOY2PageController::redirect($url);
				}
			}catch(Exception $e){
				
			}
		}
		
		if($this->mode == "code"){
			$code = $_POST["register_code"];
			
			try{
				//確認画面に遷移
				if($this->getUserByeRegisterCode($code)){
					$_SESSION["plus_user_register"] = SOY2::cast("object",$this->user);
					$url = $this->getModuleUrl("input");
					SOY2PageController::redirect($url);
				}
			}catch(Exception $e){
				//do nothing
			}
			
			$this->errors["code_error"] = true;
			$this->mode = "code_error";
		}
		
		if($this->mode == "input"){
			
			SOY2::cast($this->user,$_POST["User"]);
			
			$this->profile = (isset($_POST["Profile"])) ? $_POST["Profile"] : array();
			$this->user->setProfile($this->profile);
			$this->user->saveProfile(true);
			
			$this->profile = Plus_UserProfile::getProfile($this->user->getId());
			
			if($this->check($this->user)){
				
				//セッションに入れる
				$_SESSION["plus_user_register"] = SOY2::cast("object",$this->user);
				
				$url = $this->getModuleUrl("confirm");
				SOY2PageController::redirect($url);
			}
			
		}
		
		if($this->mode == "confirm"){
			
			//back or checkから外れた場合
			if((isset($_POST["back"]) || isset($_POST["back_x"])) || !$this->check($this->user)){
				$url = $this->getModuleUrl("input") . "?back";
				SOY2PageController::redirect($url);
			}
			
			//本登録メール送信
			//確認メールの送信
			$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
			$title = SOYCMS_DataSets::get("plus.user.mail.".$this->group->getGroupId().".complete.title","");
			$body = SOYCMS_DataSets::get("plus.user.mail.".$this->group->getGroupId().".complete.body","");
			
			$url = $this->getConfig()->getModulePageUrl("plus_user_connector.login");
			$body = str_replace("#LOGIN_URL#", $url,$body);
			$body = str_replace("#USERNAME#", $this->user->getName(),$body);
			
			try{
				@$logic->send(
					$this->user->getMailAddress(),
					$title,
					$body
				);
			}catch(Exception $e){
				
			}
			
			//本登録実行
			$this->user->setPassword($this->user->hashPassword($this->user->getPassword()));
			$this->user->setStatus(1);
			$this->user->setGroupIds(array($this->group->getGroupId()));
			$this->user->save();
			
			//プロフィールの保存
			$this->user->setProfile($this->profile);
			$this->user->saveProfile(true);
			
			//グループの登録
			Plus_UserGroup::saveGroups($this->user->getId(),array($this->group->getId()));
			
			try{
				//拡張
				PluginManager::invoke("plus.user.register",array(
					"mode" => "register",
					"userId" => $this->user->getId(),
					"groupId" => $this->groupId,
					"profiles" => $this->profile
				));
			}catch(Exception $e){
				
			}
			
			//Tokenの削除
			SOY2DAOFactory::create("Plus_UserTokenDAO")->deleteByUserId($this->user->getId());
			
			//セッションの削除
			$_SESSION["plus_user_register"] = null;
			$_SESSION["plus_user_complete_user_id"] = $this->user->getId();
			
			$url = $this->getModuleUrl("complete");
			SOY2PageController::redirect($url);
		}
	}
	
	function init(){
		$this->setChildSoy2Prefix("cms");
		
		$this->user = new Plus_User();
		$this->setConfig(PlusUserConfig::getConfig());
		
		$this->session = plus_user_get_session();
		
		//ログインしていた場合
		if($this->session && $this->session->isLoggedIn()){
			SOY2PageController::redirect(soycms_get_page_url($this->getConfig()->getMemberPageUrl()));
		}
		
		//グループ
		$this->group = SOY2DAO::find("Plus_Group",array("groupId" => $this->groupId));
		
		if(isset($_SESSION["plus_user_register"])){
			SOY2::cast($this->user,$_SESSION["plus_user_register"]);
			
			//IDが無いとダメ
			if(strlen($this->user->getId())<1){
				$_SESSION["plus_user_register"] = null;
				unset($_SESSION["plus_user_register"]);
				
				$url = $this->getModuleUrl("code") . "?failed";
				SOY2PageController::redirect($url);
			}
			
			$this->profile = Plus_UserProfile::getProfile($this->user->getId());
		}
		
		//確認コード
		if($this->mode == "code" && isset($_GET["code"])){
			$code = $_GET["code"];
			
			//確認画面に遷移
			if($this->getUserByeRegisterCode($code)){
				$_SESSION["plus_user_register"] = SOY2::cast("object",$this->user);
				$url = $this->getModuleUrl("input");
				SOY2PageController::redirect($url);
			}
			
			$this->mode = "code_error";
		}
		
		//入力画面のチェック
		if(($this->mode == "input" || $this->mode == "confirm") && !isset($_SESSION["plus_user_register"])){
			$url = $this->getModuleUrl("code") . "?failed";
			SOY2PageController::redirect($url);
		}
		
		PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.register." . $this->groupId,"新規会員登録");
	}

	function page_register($args = array()) {
		//値の受け取り
		$this->groupId = $args[0];
		if(in_array(@$args[1],$this->modes)){
			$this->mode = $args[1];
		}
		PluginManager::load("plus.user.register");
		
		WebPage::WebPage();
	}
	
	function buildPage(){
		foreach($this->modes as $mode){
			$this->addModel("mode_" . $mode,array(
				"visible" => $this->mode == $mode,
			));
		}
		
		$this->buildMailForm();
		$this->buildCodeForm();
		
		$this->buildRegisterForm();
		$this->buildConfirmForm();
		
		$this->buildCompleteForm();
	}
	
	function buildMailForm(){
		$this->addForm("mail_form");
		
		$this->addInput("mailaddress",array(
			"name" => "User[mailAddress]",
			"value" => $this->user->getMailAddress()
		));
		
		$this->addLabel("mailaddress_text",array(
			"text" => $this->user->getMailAddress()
		));
	}
	
	function buildRegisterForm(){
		$this->addForm("register_form");
		
		$this->addInput("password",array(
			"name" => "User[password]",
			"value" => $this->user->getPassword()
		));
		
		$this->addInput("password_confirm",array(
			"name" => "password_confirm",
			"value" => (isset($_POST["password_confirm"])) ? $_POST["password_confirm"] : ""
		));
		
		$this->addInput("name",array(
			"name" => "User[name]",
			"value" => $this->user->getName()
		));
		
		$this->addLabel("password_text",array(
			"text" => $this->user->getPassword()
		));
		
		$this->addLabel("name_text",array(
			"text" => $this->user->getName()
		));
		
		//エラー
		$errors = array(
			"mailaddress_require_error","mailaddress_format_error","mailaddress_unique_error",
			"password_require_error","password_format_error",
			"name_require_error","password_confirm_error"
		);
		foreach($errors as $key){
			$this->addModel($key,array(
				"visible" => (isset($this->errors[$key]) && $this->errors[$key])
			));
		}
		
		$this->addLabel("user_field_form",array(
			"html" => $this->getUserCustomFieldForm($this->groupId, $this->profile)
		));
		
		$this->addLabel("user_field_display",array(
			"html" => $this->displayUserFieldForm($this->groupId, $this->profile)
		));
		
		$this->addLabel("ext_field_display",array(
			"html" => PluginManager::display("plus.user.register",array(
			"mode" => "display",
			"groupId" => $this->groupId,
			"profiles" => $this->profile,
			"user" => $this->user
		))
		));
		
		$this->addLabel("ext_field_form",array(
			"html" => PluginManager::display("plus.user.register",array(
				"mode" => "form",
				"groupId" => $this->groupId,
				"profiles" => $this->profile,
				"user" => $this->user
			))
		));
		
		$this->addLabel("ext_field_display",array(
			"html" => PluginManager::display("plus.user.register",array(
				"mode" => "display",
				"groupId" => $this->groupId,
				"profiles" => $this->profile,
				"user" => $this->user
			))
		));
		
	}
	function buildConfirmForm(){
		$this->addForm("confirm_form");
	}
	function buildCodeForm(){
		$this->addForm("code_form");
		$this->addInput("code",array(
			"name" => "register_code",
			"value" => @$_POST["register_code"],
			"visible" => (!isset($this->errors["code_error"]))
		));
		
		
		$errors = array(
			"code_error"
		);
		foreach($errors as $key){
			$this->addModel($key,array(
				"visible" => (isset($this->errors[$key]) && $this->errors[$key])
			));
		}
	}
	function buildCompleteForm(){
		$this->addForm("login_form",array(
			"action" => $this->getConfig()->getModulePageUrl("plus_user_connector.login")
		));
		$url = $this->getModuleUrl("complete");
		$this->addForm("auto_login_form",array(
			"action" => soycms_get_page_url($url)
		));
	}
	
	/**
	 * メールアドレスの入力チェック
	 */
	function checkMailAddress($user){
		$mailaddress = $user->getMailAddress();
		
		if(strlen($mailaddress)<4){
			$this->errors["mailaddress_require_error"] = true;
		}else if(!soy2_check_valid_mailaddress($mailaddress)){
			$this->errors["mailaddress_format_error"] = true;
		}
		
		try{
			$userDAO = SOY2DAOFactory::create("Plus_UserDAO");
			$user = $userDAO->getByMailAddress($mailaddress);
			
			//仮登録ならOK
			if($user->getStatus() == 0){
				$this->user = $user;
			}else{
				$this->errors["mailaddress_unique_error"] = true;
			}
		}catch(Exception $e){
			//ok
		}
		
		if(empty($this->errors))return true;
		
		return false;
	}
	
	/**
	 * validationを行う
	 * @param unknown_type $user
	 * @return boolean
	 */
	function check($user){
		
		if(strlen($user->getPassword())<4){
			$this->errors["password_require_error"] = true;
		}
		
		if(strlen($user->getName())<1){
			$this->errors["name_require_error"] = true;
		}
		
		if(isset($_POST["password_confirm"]) && $_POST["password_confirm"] != $user->getPassword()){
			$this->errors["password_confirm_error"] = true;
		}
		
		//fields
		$fields = Plus_UserProfile::getFields();
		$settings = Plus_UserProfile::getSettings($this->groupId . "_register");
		
		foreach($settings as $key => $setting){
			if(!$setting)continue;
			$require = ($setting > 10);
			if(!$require)continue;
			if($setting < 10)continue;
			
		
			if(!isset($fields[$key]))continue;
		
			/* @var $value SOYCMS_ObjectCustomField */
			$field = $fields[$key];
			$value = (isset($this->profile[$key])) ? $this->profile[$key] : null;
			
			if(is_object($value) && $value->isEmpty()){
				$this->errors[$key] = true;
			}else if(empty($value)){
				$this->errors[$key] = true;
			}
			
		}
		
		//エラーチェック
		$_errors = PluginManager::invoke("plus.user.register",array(
			"mode" => "validate",
			"userId" => $this->user->getId(),
			"groupId" => $this->groupId,
			"user" => $this->user,
			"profiles" => $this->profile
		))->getValidateErrors();
		
		$this->errors = array_merge($this->errors,$_errors);
		
		if(empty($this->errors))return true;
		
		return false;
	}
	
	function getUserByeRegisterCode($code){
		$token = SOY2DAO::find("Plus_UserToken",array("token" => $code));
		
		//ok
		if($token && $token->getLimit() > time()){
			$userId = $token->getUserId();
			$user = SOY2DAO::find("Plus_User",$userId);
			$user->setPassword("");
			$this->user = $user;
			
			return true;
		}
		
		return false;
	}
	
	function getTemplateFilePath(){
		$class = str_replace("page_","",get_class($this));
		$class .= "_" . $this->groupId;
		return SOYCMS_SITE_DIRECTORY . ".template/_user/{$class}/template.html";
	}
	
	/* カスタムフィールド関連 */
	
	/**
	 * カスタムフィールド入力フォームを表示
	 */
	function getUserCustomFieldForm($groupId,$profiles){
		$fields = Plus_UserProfile::getFields();
		$settings = Plus_UserProfile::getSettings($groupId . "_register");
		$html = array();
		SOY2::import("site.logic.field.SOYCMS_ObjectCustomFieldBuilder");
		$builder = SOYCMS_ObjectCustomFieldBuilder::prepare("register-form","Profile");
		
		foreach($settings as $key => $setting){
			if(!$setting)continue;
			if($setting < 10)continue;
				
			try{
				$field = $fields[$key];
				$value = (isset($profiles[$key])) ? $profiles[$key] : null;
		
				$require = ($setting > 10);
				$error = false;
				if(isset($profiles[$key]) || isset($_POST["Profile"])){
					$error = empty($value);
					if(is_object($value))$error = $value->isEmpty();
					if(is_array($value)){
						foreach($value as $_value){
							$error = empty($value);
							if($error)break;
								
							if(is_object($_value))$error = $_value->isEmpty();
							if($error)break;
						}
					}
				}
		
				if(file_exists(SOYCMS_SITE_DIRECTORY . ".field/user/register/" . $key . ".html")){
					ob_start();
					include(SOYCMS_SITE_DIRECTORY . ".field/user/register/" . $key . ".html");
					$html[] = ob_get_clean();
					continue;
				}
		
		
				$html[] = '<div class="form-section">';
				$html[] = 	'<div class="title">';
				$html[] = 		'<h4>'.$field->getName(). (($require) ? "*" : "") . '</h4>';
				$html[] = 	'</div>';
				$html[] = 	'<div class="item">';
				if($require && $error){
					$html[] = '<p class="error">'.$field->getName().'は必須です。</p>';
				}
				$html[] = 		'<p>' . $field->getDescription() . '</p>';
				$html[] = 		$builder->buildForm($field,$value);
				$html[] = 	'</div>';
				$html[] = '</div>';
			}catch(Exception $e){
		
			}
		}
		
		return implode("",$html);
	}
	
	/**
	 * カスタムフィールドの入力確認画面を表示
	 */
	function displayUserFieldForm($groupId,$profiles){
		$fields = Plus_UserProfile::getFields();
		$settings = Plus_UserProfile::getSettings($groupId . "_register");
		$html = array();
		
		foreach($settings as $key => $setting){
			if(!$setting)continue;
				
			$field = $fields[$key];
			$value = (isset($profiles[$key])) ? $profiles[$key] : null;
				
			$require = ($setting > 10);
			
			if(file_exists(SOYCMS_SITE_DIRECTORY . ".field/user/register/" . $key . "_confirm.html")){
				ob_start();
				include(SOYCMS_SITE_DIRECTORY . ".field/user/register/" . $key . "_confirm.html");
				$html[] = ob_get_clean();
				continue;
			}
				
			$html[] = '<div class="form-section">';
			$html[] = 	'<div class="title">';
			$html[] = 		'<h4>'.$field->getName(). '</h4>';
			$html[] = 	'</div>';
			$html[] = 	'<div class="item">';
			if(is_array($value)){
				$_html = array();
				foreach($value as $_value){
					$_html[] = $_value->getValue();
				}
				$html[] = (empty($_html)) ? "-" : implode(",",$_html);
			}else if(is_object($value)){
				$html[] = (strlen($value->toString())<1) ? "-" : $value->toString();
			}
			$html[] = 	'</div>';
			$html[] = '</div>';
		}
		
		return implode("",$html);
	}
}

?>