<?php

class Plus_UserProfile{
	
	public static function getProfile($userId){
		return SOYCMS_ObjectCustomField::getValues("user",$userId);
	}
	
	public static function saveProfile($userId,$values,$flag = false){
		$def_values = SOYCMS_ObjectCustomField::getValues("user",$userId);
		foreach($def_values as $fieldId => $_value){
			if(!isset($values[$fieldId])){
				$values[$fieldId] = $_value;
			}
		}
		SOYCMS_ObjectCustomField::setValues("user",$userId,$values);
	}
	
	public static function getFields(){
		return SOYCMS_ObjectCustomFieldConfig::loadConfig("user");
	}
	
	public static function setFields($fields){
		SOYCMS_ObjectCustomFieldConfig::saveConfig("user",$fields);
	}
	
	/**
	 * setting
	 * array(
	 * 	key => 0(無し), 1(管理側のみ),2(ユーザ入力任意),3(ユーザ入力必須)
	 * )
	 */
	public static function getSettings($type = "common"){
		if(is_array($type)){
			$res = array();
			
			foreach($type as $_type){
				$settings = SOYCMS_DataSets::get("plus.user.profile." . $_type, array());
				foreach($settings as $key => $value){
					if(!$value)continue;
					if(!isset($res[$key]))$res[$key] = 0;
					$res[$key] = max($res[$key],$value);
				}
			}
			return $res;
		}else{
			$settings = SOYCMS_DataSets::get("plus.user.profile." . $type, array());
			return $settings;
		}
	}
	
	public static function setSettings($type = "common",$setting){
		SOYCMS_DataSets::put("plus.user.profile." . $type, $setting);
	}
	
	function check(){
		return true;
	}
	
}

?>