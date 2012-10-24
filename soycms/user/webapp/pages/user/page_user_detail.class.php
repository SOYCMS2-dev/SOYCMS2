<?php
/**
 * @title 管理者詳細
 */
class page_user_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["User"])){
			$old = clone($this->user);
			SOY2::cast($this->user,$_POST["User"]);
			
			if(isset($_POST["new_password"]) && strlen($_POST["new_password"]) > 0){
				$this->user->setPassword($this->user->hashPassword($_POST["new_password"]));
				$this->user->save();
			}else{
				
				PluginManager::load("plus.user.profile");
				$profiles = Plus_UserProfile::getProfile($this->user->getId());
				
				if(isset($_POST["Profile"])){
					$this->user->setProfile($_POST["Profile"]);
					$this->user->saveProfile();
				}
				
				if($this->check($this->user)){
					if(isset($_POST["groupIds"])){
						$res = Plus_UserGroup::saveGroups($this->user->getId(),$_POST["groupIds"]);
						$this->user->setGroupIds($res);
					}
					$this->user->save();
					
					PluginManager::invoke("plus.user.profile",array(
						"mode" => "post",
						"userId" => $this->id
					));
					
					$this->jump("/user/detail/" . $this->id . "?updated");
				
				//failed
				}else{
					if(isset($_POST["Profile"])){
						$this->user->setProfile($profiles);
						$this->user->saveProfile();
					}
					
					$_GET["failed"] = 1;
				}
			
			}
		}
		
	}
	
	private $id;
	private $errors = array();
	
	/**
	 * @var Plus_User
	 */
	private $user;
	private $profiles = array();
	
	function init(){
		
		try{
			$this->user = SOY2DAO::find("Plus_User",$this->id);
		}catch(Exception $e){
			$this->jump("/user");
		}
	}

	function page_user_detail($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->addLabel("user_name_text",array(
			"text" => (strlen($this->user->getName()) > 0) ? $this->user->getName() : "ユーザ"
		));
		
		$this->createAdd("detail_form","_class.form.UserForm",array(
			"user" => $this->user,
			"action" => soycms_create_link("user/detail/" . $this->id)
		));
		
		$this->addLink("create_entry_link",array(
			"link" => soycms_create_link("../site/entry/create") . "?attribute[plus_user_entry_author]=" . $this->user->getId()
		));
		
		$this->createAdd("entry_list","plus_user_detail_EntryList",array(
			"list" => $this->getEntries()
		));
		
		$groups = explode(",",$this->user->getGroupIds());
		$groups[] = "common";
		$settings = Plus_UserProfile::getSettings($groups);
		
		$this->createAdd("field_list","_class.list.ProfileList",array(
			"list" => Plus_UserProfile::getFields(),
			"userId" => $this->id,
			"setting" => $settings,
			"formName" => "Profile"
		));
		
		SOY2::import("site.logic.field.SOYCMS_ObjectCustomFieldBuilder");
		PluginManager::load("plus.user.profile");
		$this->addLabel("ext_field_form",array(
			"html" => PluginManager::display("plus.user.profile",array(
				"mode" => "form",
				"userId" => $this->id,
				"helper" => SOYCMS_ObjectCustomFieldBuilder::prepare("user_detail_profile","Profile")
			))
		));
		
		
		
		//グループ
		$this->createAdd("group_list","_class.list.GroupCheckList",array(
			"list" => SOY2DAO::find("Plus_Group"),
			"selected" => explode(",",$this->user->getGroupIds()),
			"name" => "groupIds[]"
		));
		
		/* エラー */
		foreach(array("login_id","name","mail_address") as $key){
			$this->addModel($key . "_error",array(
				"visible" => @$this->errors[$key]
			));
		}
		
	}
	
	function getEntries(){
		$res = array();
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao = SOY2DAOFactory::create("SOYCMS_EntryAttributeDAO");
		$result = $dao->getByValues("plus_user_entry_author", $this->user->getId());
		
		foreach($result as $obj){
			try{
				$res[] = $entryDAO->getById($obj->getEntryId());
			}catch(Exception $e){
				$dao->clearByParams($obj->getEntryId(),"plus_user_entry_author");
			}
		}
		
		return $res;
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
			$this->errors["name"] = true;
		}
		
		$fields = Plus_UserProfile::getFields();
		$profiles = Plus_UserProfile::getProfile($user->getId());
		$settings = (isset($_POST["groupIds"])) ? Plus_UserProfile::getSettings($_POST["groupIds"]) : array();
		
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
			"profiles" => $profiles
		))->getValidateErrors();
		
		$this->errors = array_merge($this->errors,$_errors);
		
		return (count($this->errors) < 1);
	}
}

class plus_user_detail_EntryList extends HTMLList{
	
	private $link;
	
	function init(){
		$this->link = soycms_create_link("../site/entry/detail");
	}
	
	function populateItem($entity){
		$this->addLink("entry_detail_link",array(
			"link" => $this->link . "/" . $entity->getId()
		));
		
		$this->addLabel("entry_title",array(
			"text" => $entity->getTitle()
		));
	}
	
}