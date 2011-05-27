<?php

class page_page_label_custom extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["add"]) && strlen($_POST["NewField"]["fieldId"])){
			
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
			$config = new SOYCMS_ObjectCustomFieldConfig();
			SOY2::cast($config,$_POST["NewField"]);
			
			$configs[$config->getFieldId()] = $config;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("label",$configs);
			
			$this->jump("/page/label/custom?created");
		}
		
		if(isset($_POST["remove"])){
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
			$fieldId = $_POST["field_id"];
			unset($configs[$fieldId]);
			SOYCMS_ObjectCustomFieldConfig::saveConfig("label",$configs);
			
			$this->jump("/page/label/custom?removed");
		}
		
		if(isset($_POST["save_config"]) && isset($_POST["field_id"])){
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
			$fieldId = $_POST["field_id"];
			$config = (isset($configs[$fieldId])) ? $configs[$fieldId] : new SOYCMS_ObjectCustomFieldConfig();
			
			SOY2::cast($config,$_POST["fields"]);
			
			//FieldIdを書き換えた場合
			if($fieldId != $config->getFieldId()){
				$res = array();
				foreach($configs as $key => $config){
					if($key == $fieldId){
						$res[$config->getFieldId()] = $config;
						continue;
					}
					$res[$key] = $config;
				}
				$configs = $res;
			}
			
			
			$configs[$config->getFieldId()] = $config;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("label",$configs);
			
			$this->jump("/page/label/custom?updated#field_" . $config->getFieldId());
		}
		
		if(isset($_POST["save_orders"])){
			$keys = array_keys($_POST["FieldOrder"]);
			
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
			
			$res = array();
			foreach($keys as $key){
				if(!isset($configs[$key]))continue;
				$res[$key] = $configs[$key];
			}
			$configs = $res;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("label",$res);
			
			$this->jump("/page/label/custom?updated");
		}
		
		$this->jump("/page/label/custom");
		
	}

	function page_page_label_custom() {
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
	}
	
	function buildForm(){
		
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
		
		$this->addForm("add_form");
		$this->addSelect("field_type_select",array(
			"name" => "NewField[type]",
			"options" => SOYCMS_ObjectCustomFieldConfig::getTypes()
		));
		$this->addInput("field_id_input",array(
			"name" => "NewField[fieldId]",
			"value" => "",
		));
		$this->addInput("field_label_input",array(
			"name" => "NewField[label]",
			"value" => ""
		));
		
		$this->createAdd("field_list","_class.list.CustomFieldConfigList",array(
			"list" => $configs
		));
		
		//表示順
		$this->addForm("list_form");
	}
	
	function buildPage(){
		
	}
}
?>