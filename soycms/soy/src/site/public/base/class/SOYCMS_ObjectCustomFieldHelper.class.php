<?php
/**
 * カスタムフィールド表示用のHelperクラス
 */
class SOYCMS_ObjectCustomFieldHelper {
	
	public static function build($htmlObj,$config,$field){
		//複数では無い場合
		if(is_array($field) && isset($field[-1])){
			$field = $field[-1];
		}
		
		if(is_object($field)){
			
			if($field->getType() == "group"){
				$_value = $field->getValueObject();
				$fields = $config->getFields();
				foreach($fields as $key => $_config){
					if(strlen($key)<1)continue;
					$value = (isset($_value[$key])) ? $_value[$key] : $_config->getValueObject();
					$value->setFieldId($key);
					self::_build($htmlObj,$value);
				}
				
				return;
			}
			
			return self::_build($htmlObj,$field);
		}
		
		//複数の場合
		$fieldId = $config->getFieldId();
		$htmlObj->createAdd($fieldId . "_list", "SOYCMS_ObjectCustomFieldListComponent",array(
			"list" => $field,
			"soy2prefix" => "cms",
			"config" => $config
		));
	}
	
	private static function _build($htmlObj,$field){
		
		$type = $field->getType();
		$value = $field->getValue();
		if(strlen($value)<1)$value = $field->getText();
		
		//画像のみ
		if($field->getType() == "image"){
			$array = $field->getValueObject();
			$src = @$array["src"];
			$title = @$array["title"];
			$alt = @$array["alt"];
			$value = $src;
			$htmlObj->addImage($field->getFieldId() . "_image",array(
				"src" => $value,
				"attr:title" => $title,
				"attr:alt" => $alt,
				"soy2prefix" => "cms",
				"visible" => (strlen($src) > 0)
			));
		}
		
		//url
		if($field->getType() == "url"){
			$array = @$field->getValueObject();
			$href = @$array["href"];
			$title = @$array["title"];
			$value = $href;
			
			$htmlObj->addLink($field->getFieldId() . "_link",array(
				"link" => $value,
				"attr:title" => @$title,
				"soy2prefix" => "cms",
			));
		}
		
		$htmlObj->createAdd($field->getFieldId() . "_sets","SOYCMS_ObjectCustomFieldSetComponent",array(
			"soy2prefix" => "cms",
			"value" => $value
		));
		
		if($field->getType() == "date" || $field->getType() == "datetime"){
			
			$htmlObj->createAdd($field->getFieldId(),"DateLabel",array(
				"value" => $value,
				"soy2prefix" => "cms",
			));
			
			return;
		}
		
		//時刻のフィールド
		if($field->getType() == "time"){
			$htmlObj->createAdd($field->getFieldId(),"DateLabel",array(
				"value" => $value,
				"soy2prefix" => "cms",
				"defaultFormat" => "H:i"
			));
			
			return;
		} 
		
		$htmlObj->addLabel($field->getFieldId(),array(
			"html" => $value,
			"soy2prefix" => "cms",
		));
		
		
	}
	
}

class SOYCMS_ObjectCustomFieldSetComponent extends SOYBodyComponentBase{
	private $value;
	
	function execute(){
		
		$this->addModel("empty",array(
			"visible" => (strlen($this->value)<1),
			"soy2prefix" => $this->_soy2_prefix		
		));
		
		$this->addModel("not_empty",array(
			"visible" => (strlen($this->value)>0),
			"soy2prefix" => $this->_soy2_prefix		
		));
		
		$this->addLabel("field_value",array(
			"html" => $this->value,
			"soy2prefix" => $this->_soy2_prefix
		));
		
		parent::execute();
	}
		
}

class SOYCMS_ObjectCustomFieldListComponent extends HTMLList{
	
	private $config;
	
	function populateItem($entity){
		if(!$entity instanceof SOYCMS_ObjectCustomField){
			$entity = $this->config->getValueObject();
		}
		SOYCMS_ObjectCustomFieldHelper::build($this,$this->config,$entity);
	}
	
	function setConfig($config){
		$this->config = $config;
	}
	
}
?>