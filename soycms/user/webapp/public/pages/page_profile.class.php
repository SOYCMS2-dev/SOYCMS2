<?php

class page_profile extends PlusUserWebPageBase{
	
	private $mode = "preview";
	private $userId;
	private $errors = array();
	
	/**
	 * @var Plus_User
	 */
	private $user;
	private $profiles = array();
	
	function doPost(){
		
		if(isset($_POST["User"])){
			$_POST["User"]["password"] = $this->user->getPassword();
			$value = SOY2::cast((object)array("name" => ""),$_POST["User"]);
			SOY2::cast($this->user,$value);
		}
		
		if(isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0){
			$width_max = SOYCMS_DataSets::get("plus_user_connector.profile.image_width","240");
			$this->user->uploadImage($_FILES["profile_image"]["tmp_name"],$width_max);
		}
		
		if(isset($_FILES["user_images"])){
			$names = $_FILES["user_images"]["name"];
			$userdir = SOYCMS_SITE_DIRECTORY . "files/users/" . $this->user->getId() . "/";
			$userpath = soycms_union_uri(SOYCMS_SITE_URL , "files/users/" . $this->user->getId() .  "/");
			if(!file_exists($userdir))soy2_mkdir($userdir);
			$width_max = SOYCMS_DataSets::get("plus_user_connector.profile.image_width","240");
			
			foreach($names as $key => $value){
				if(!is_array($value)){
					if(strlen($value)<1)continue;
					$tmpname = $_FILES["user_images"]["tmp_name"][$key];
					$type = $_FILES["user_images"]["type"][$key];
					if($type != "image/jpeg")continue;
					
					$filename = md5($tmpname . time());
					
					move_uploaded_file(
						$tmpname,
						$userdir . $filename . ".jpeg"
					);
					
					soy2_resizeimage($userdir . $filename . ".jpeg", $userdir . $filename . ".jpeg", $width_max);
					
					$url = $userpath . $filename . ".jpeg";
					$_POST["Profile"][$key] = array(
						"src" => $url
					);
					
				}else{
					foreach($value as $index => $_value){
						if(strlen($_value)<1)continue;
						$tmpname = $_FILES["user_images"]["tmp_name"][$key][$index];
						$type = $_FILES["user_images"]["type"][$key][$index];
						$filename = md5($tmpname . time());
						
						if($type != "image/jpeg")continue;
					
						move_uploaded_file(
							$tmpname,
							$userdir . $filename . ".jpeg"
						);
						
						soy2_resizeimage($userdir . $filename . ".jpeg", $userdir . $filename . ".jpeg", $width_max);
						
						$url = $userpath . $filename . ".jpeg";
						if(!isset($_POST["Profile"][$key]))$_POST["Profile"][$key] = array();
						$_POST["Profile"][$key][$index] = array(
							"src" => $url
						);
					}
				}
				
			}
			
		}
		
		$old_profiles = Plus_UserProfile::getProfile($this->user->getId());
		if(isset($_POST["Profile"])){
			Plus_UserProfile::saveProfile($this->user->getId(),$_POST["Profile"]);
		}
		
		if(strlen(@$_POST["new_password"]) > 0 && @$_POST["new_password"] == @$_POST["new_password_confirm"]){
			$this->user->setPassword($this->user->hashPassword($_POST["new_password"]));
		}
		
		//必須チェック
		if($this->check($this->user)){
			
			$prefix = SOYCMS_DataSets::get("plus_user_connector.profile.prefix","members");
			$this->user->setProfileUrl(soycms_get_page_url($prefix,"user-" . $this->user->getId()));
			$this->user->save();
			
			$this->getSession()->setName($this->user->getName());
		
			PluginManager::invoke("plus.user.profile",array(
				"mode" => "user_post",
				"userId" => $this->getSession()->getId()
			));
			
			PlusUserApplicationHelper::getController()->jumpToModule("plus_user_connector.profile","",array(
				"updated" => 1
			));
		}
		
		$_GET["failed"] = true;
		
		//プロフィールを戻す
		$this->profiles = Plus_UserProfile::getProfile($this->user->getId());
		Plus_UserProfile::saveProfile($this->user->getId(),$old_profiles);
	}
	
	function init(){
		$this->user = $this->getSession()->getUser();
		$this->userId = $this->user->getId();
		PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.profile","登録情報の変更");
	}

	function page_profile($args) {
		
		if(count($args)>0){
			$this->mode = $args[0];
		}
		
		PluginManager::load("plus.user.profile");
		
		WebPage::WebPage();
		
		
	}
	
	
	function buildForm(){
		
		$this->addUploadForm("profile_form",array(
			"action" => PlusUserApplicationHelper::getModulePageUrl("plus_user_connector.profile")
		));
		
		$this->addInput("profile_user_name",array(
			"name" => "User[name]",
			"value" => $this->user->getName()
		));
		
		$this->addImage("profile_user_image",array(
			"src" => $this->user->getProfileImageUrl() . "?t=" . time()
		));
		
		$this->addLink("profile_link",array(
			"link" => $this->user->getProfileUrl()
		));
		
		SOY2::import("site.logic.field.SOYCMS_ObjectCustomFieldBuilder");
		$sufffix = $this->getPluginForm();
		
		$this->addLabel("ext_field_form",array(
			"html" => $this->displayForm() . $sufffix
		));
		
		$this->addLabel("plugin_field_form",array(
			"html" => $sufffix
		));
		
		//エラー周り
		foreach(array("name_require","password_confirm") as $key){
			$this->addModel($key . "_error",array(
				"visible" => @$this->errors[$key . "_error"]
			));
		}
		
	}
	
	function buildPage(){
		
		$this->addModel("mode_preview",array(
			"visible" => $this->mode == "preview"
		));
		
		$this->addModel("mode_edit",array(
			"visible" => $this->mode == "edit"
		));
		
	}
	
	function displayForm(){
		
		$helper = SOYCMS_ObjectCustomFieldBuilder::prepare("profile-field","Profile","user");
		$helper->setFilter("image",array($this,"buildFormForImage"));
		
		$profiles = ($this->profiles) ? $this->profiles : Plus_UserProfile::getProfile($this->getUserId());
		$fields = Plus_UserProfile::getFields();
		$settings = Plus_UserProfile::getSettings(plus_user_get_session()->getGroups());
		$html = array();
	
		foreach($settings as $key => $setting){
			if(!$setting)continue;
			if(!isset($fields[$key]))continue;
			if($setting < 10)continue; //管理側でのみ表示は隠す
	
			$field = $fields[$key];
			$value = (isset($profiles[$key])) ? $profiles[$key] : null;
	
			$require = ($setting > 10);
			
			if(file_exists(SOYCMS_SITE_DIRECTORY . ".field/user/profile/" . $key . ".html")){
				ob_start();
				include(SOYCMS_SITE_DIRECTORY . ".field/user/profile/" . $key . ".html");
				$html[] = ob_get_clean();
				continue;
			}
	
			$html[] = '<div class="form-section">';
			$html[] = 	'<div class="title">';
			$html[] = 		'<h4>'.$field->getName(). (($require) ? "*" : "") . '</h4>';
			$html[] = 	'</div>';
			$html[] = 	'<div class="item">';
			if(strlen($field->getDescription())>0)$html[] = '<p>' . $field->getDescription() . '</p>';
			$html[] = $helper->buildForm($field,$value);
			$html[] = 	'</div>';
			$html[] = '</div>';
		}
		
		return implode("",$html);
	}
	
	function getPluginForm(){
		$helper = SOYCMS_ObjectCustomFieldBuilder::prepare("profile-field","Profile","user");
		$helper->setFilter("image",array($this,"buildFormForImage"));
		
		$profiles = ($this->profiles) ? $this->profiles : Plus_UserProfile::getProfile($this->getUserId());
		
		$suffix_html = PluginManager::display("plus.user.profile",array(
			"mode" => "user_form",
			"helper" => $helper,
			"userId" => $this->getSession()->getId(),
			"user" => $this->user,
			"profiles" => $profiles
		));
		return $suffix_html;
	}
	
	function buildFormForImage($helper,$field,$value){
	
		$key = $field->getFieldId();
		$valueObj = new SOYCMS_ObjectCustomField();
		$valueObj->setValue($value);
	
		$html = array();
	
		if($field->isMulti()){
			$array = (is_array($value)) ? $value : $valueObj->getValueObject();
				
			for($i=0;$i<$field->getMultiMax();$i++){
				if(!isset($array[$i]))$array[$i] = new SOYCMS_ObjectCustomField();
				$_array = $array[$i]->getValueObject();
	
				$html[] = '<span>'.($i+1).' </span>';
	
				if(strlen($_array["src"])>0){
					$html[] = '<img src="'.$_array["src"].'" style="width:150px;" />';
				}
	
				$html[] = '<p>';
				$html[] = '<input type="file" name="user_images['.$key.']['.$i.']" />';
				$html[] = '<input type="hidden" name="Profile['.$key.']['.$i.'][src]" value="'.@$_array["src"].'" />';
				$html[] =  '</p>';
			}
		}else{
			$array = $valueObj->getValueObject();
			if(strlen($array["src"])>0){
				$html[] = '<img src="'.$array["src"].'" style="width:150px;" />';
			}
	
			$html[] = '<input type="file" name="user_images['.$key.']" />';
			$html[] = '<input type="hidden" name="Profile['.$key.'][src]" value="'.htmlspecialchars(@$array["src"]).'" />';
	
		}
	
		return implode("",$html);
	}
	
	/**
	 * validationを行う
	 * @param unknown_type $user
	 * @return boolean
	 */
	function check($user){
		
		$this->errors = array();
		
		//名前
		if(strlen($user->getName())<1){
			$this->errors["name_require_error"] = true;
		}
			
		//パスワード
		if(strlen(@$_POST["new_password"]) > 0 && @$_POST["new_password"] != @$_POST["new_password_confirm"]){
			$this->errors["password_confirm_error"] = true;
		}
		
		$fields = Plus_UserProfile::getFields();
		$profiles = Plus_UserProfile::getProfile($this->getUserId());
		$settings = Plus_UserProfile::getSettings(plus_user_get_session()->getGroups());
		
		foreach($settings as $key => $setting){
			if(!$setting)continue;
			if($setting < 10)continue;
			if(!isset($fields[$key]))continue;
			
			$value = (isset($profiles[$key])) ? $profiles[$key] : null;
			
			$require = ($setting > 10);
			if($require && !$value){
				$this->errors[$key] = true;
			}
			
			if($require && is_object($value) && $value->isEmpty()){
				$this->errors[$key] = true;
			}
		}
		
		//エラーチェック
		$_errors = PluginManager::invoke("plus.user.profile",array(
			"mode" => "validate",
			"userId" => $this->user->getId(),
			"user" => $this->user,
			"profiles" => $profiles,
			"errors" => $this->errors
		))->getValidateErrors();
		
		$this->errors = array_merge($this->errors,$_errors);
		
		return (count($this->errors) < 1);
	}
	
	function getProfileEntry($userId){
		$mapping = SOYCMS_DataSets::get("site.url_mapping");
		$prefix = SOYCMS_DataSets::get("plus_user_connector.profile.prefix","members");
		$dirId = (isset($mapping[$prefix])) ? $mapping[$prefix] : null;
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$uri = "user-" . $userId;
	
		//ディレクトリが無い場合は作成しない
		if(is_null($dirId))return null;
	
		try{
			$entry = $dao->getByUri($uri,$dirId);
		}catch(Exception $e){
			$entry = new SOYCMS_Entry();
			$entry->setUri($uri);
		}
	
		$entry->setDirectory($dirId);
		$entry->setUri($uri);
	
		return $entry;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function setUserId($userId){
		$this->userId = $userId;
		return $this;
	}
}
?>