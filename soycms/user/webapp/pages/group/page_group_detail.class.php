<?php

class page_group_detail extends SOYCMS_WebPageBase{
	
	private $group;
	private $id;
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			$this->group->delete();
			$this->jump("/group/?deleted");
		}
		
		if(isset($_POST["save"])){
			SOY2::cast($this->group,$_POST["Group"]);
			$this->group->save();
			
			//新規登録
			if($this->group->getConfigure("register")){
				$config = PlusUserConfig::getConfig();
				$mappings = $config->getModuleMapping();
				$key = "plus_user_connector.register." . $this->group->getGroupId();
				if(!isset($mappings[$key]))$mappings[$key] = array(
					"active" => true,
					"url" => $this->group->getGroupId() . "/register"
				);
				$mappings[$key]["active"] = true;
				$config->setModuleMapping($mappings);
				
				//テンプレートのチェック
				$template = SOYCMS_Template::load("_user/register_" . $this->group->getGroupId());
				if(!$template){
					$template = new SOYCMS_Template();
					$template->setId("_user/register_" . $this->group->getGroupId());
					$template->setType("user");
					$template->setTypeText("ユーザ管理");
					
					//TODO カスタムフィールドに応じて修正
					$content = file_get_contents(PLUSUSER_ROOT_DIR . "template/register/template.html");
					$template->setTemplate($content);
				}
				
				$template->setName($this->group->getName() . "新規ユーザ登録用テンプレート");
				$template->save();
			}
			
			$this->jump("/group/detail/".$this->id."?updated");
		}
		
		if(isset($_POST["save_view_config"])){
			Plus_UserProfile::setSettings($this->group->getGroupId(),@$_POST["view"]);
			
			$view = (isset($_POST["view"])) ? @$_POST["view"] : array();
			if(!isset($_POST["register"]))$_POST["register"] = array();
			foreach($_POST["register"] as $key => $value){
				if(!isset($view[$key]))$value = null;
				if(@$view[$key] == 0)$value = 0;
				$_POST["register"][$key] = $value;
			}
			Plus_UserProfile::setSettings($this->group->getGroupId() . "_register",@$_POST["register"]);
			$this->jump("/group/detail/".$this->id."?updated");
		}
		
		
		$this->jump("/group/detail/".$this->id."?failed");
		
	}
	
	function init(){
		try{
			$this->group = SOY2DAO::find("Plus_Group",$this->id);
		}catch(Exception $e){
			$this->jump("/group?failed");
		}
	}
	
	function page_group_detail($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		$this->addLabel("group_name_text",array(
			"text" => $this->group->getName()
		));
		
		$this->addLAbel("group_id_text",array(
			"text" => $this->group->getGroupId()
		));
		
		$this->addModel("is_group_register",array(
			"visible" => $this->group->getConfigure("register")
		));
		
		$this->addLink("group_register_template_link",array(
			"link" => soycms_create_link("../site/page/template/detail?id=_user/register_" . $this->group->getGroupId())
		));
	}
	
	function buildForm(){
		$this->createAdd("form","_class.form.GroupForm",array(
			"group" => $this->group
		));
		
		$this->addForm("field_form");
		
		//フィールドを追加。共通で追加されている場合は隠す
		$_fields = Plus_UserProfile::getFields();
		$fields = array();
		$common_setttings = Plus_UserProfile::getSettings();
		foreach($_fields as $field){
			if(isset($common_setttings[$field->getFieldId()]) && $common_setttings[$field->getFieldId()] > 0){
				continue;
			}
			
			$fields[] = $field;
		}
		
		$this->createAdd("field_list","page_group_detail_FieldList",array(
			"list" => $fields,
			"settings" => array(Plus_UserProfile::getSettings($this->group->getGroupId()),Plus_UserProfile::getSettings($this->group->getGroupId() . "_register"))
		));
	}
	
}


class page_group_detail_FieldList extends HTMLList{
	
	private $settings = array();
	
	function populateItem($entity){
		list($profile,$register) = $this->getSettings();
		
		$this->addLabel("field_name",array("text" => $entity->getName()));
		
		$this->createAdd("profile_type_select","_class.form.CustomFieldConfigSelect",array(
			"name" => "view[".$entity->getFieldId()."]",
			"selected" => (isset($profile[$entity->getFieldId()])) ? $profile[$entity->getFieldId()] : 0
		));
		
		$this->createAdd("register_type_select","_class.form.CustomFieldConfigSelect",array(
			"name" => "register[".$entity->getFieldId()."]",
			"selected" => (isset($register[$entity->getFieldId()])) ? $register[$entity->getFieldId()] : 0,
			"visible" => (isset($profile[$entity->getFieldId()]) && $profile[$entity->getFieldId()] > 0),
			"mode" => "register"
		));
	}
	
	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}

	function getSettings() {
		return $this->settings;
	}
	function setSettings($settings) {
		$this->settings = $settings;
	}
}
?>