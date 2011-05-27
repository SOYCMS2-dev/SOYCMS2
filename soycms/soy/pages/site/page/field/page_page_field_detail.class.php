<?php

class page_page_field_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"]) && soy2_check_token()){
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			unset($configs[$this->id]);
			SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$configs);
			$this->jump("page/field?deleted");
		}
		
		if(isset($_POST["config"])){
			$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			$this->config = SOY2::cast($this->config,$_POST["config"]);
			$configs[$this->id] = $this->config;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$configs);
			$this->jumpToDetail();
		}
		
		if(isset($_POST["fields"]) && isset($_POST["field_id"])){
			$fieldId = $_POST["field_id"];
			$fields = $this->config->getFields();
			
			if(isset($fields[$fieldId])){
				$keys = array_keys($fields);
				$index = array_search($fieldId,$keys);
				$obj = $fields[$fieldId];
				SOY2::cast($obj,$_POST["fields"]);
				$keys[$index] = $obj->getFieldId();
				$fields[$obj->getFieldId()] = $obj;
				
				$res = array();
				foreach($keys as $key){
					$res[$key] = $fields[$key];
				}
				
				$this->config->setFields($res);
			}
			
			$this->jumpToDetail();
		}
		
		if(isset($_POST["NewField"])){
			$field = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$_POST["NewField"]);
			$fields = $this->config->getFields();
			$fields[$field->getFieldId()] = $field;
			$this->config->setFields($fields);
			$this->jumpToDetail();
		}
		
		if(isset($_POST["FieldOrder"])){
			$fields = $this->config->getFields();
			
			$res = array();
			foreach($_POST["FieldOrder"] as $key => $val){
				if(!isset($fields[$key]))continue;
				$res[$key] = $fields[$key];
			}
			$this->config->setFields($res);
			
			$this->jumpToDetail();
		}
		
	}
	
	function jumpToDetail(){
		if(isset($_GET["layer"])){
			echo "<html>";
			echo "<head><script type=\"text/javascript\">";
			echo "var url = window.parent.location.href;";
			echo "if(window.parent.location.search.length==0){url+='?';}";
			echo "else if(!window.parent.location.search.match(/updated/)){url+='&';}";
			echo "if(!window.parent.location.search.match(/updated/))url+='updated';";
			echo "window.parent.location.href = url;";
			echo "</script></head>";
			echo "</html>";
			exit;
		}
		$this->jump("page/field/detail/" . $this->id . "?updated");
	}
	

	private $id;
	private $config;
	
	function init(){
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
		$this->config = $configs[$this->id];
	} 

	function page_page_field_detail($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		$this->buildForm();
	}
	
	function buildForm(){
		$this->createAdd("form","_class.form.CustomFieldConfigForm",array(
			"config" => $this->config
		));
		
		$this->addLabel("field_name",array("text" => $this->config->getName()));
		
		$this->addModel("is_detail",array(
			"visible" => (!isset($_GET["layer"]))
		));
	}
}
?>