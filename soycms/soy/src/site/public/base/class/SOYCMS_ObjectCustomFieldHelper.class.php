<?php
/**
 * カスタムフィールド表示用のHelperクラス
 */
class SOYCMS_ObjectCustomFieldHelper {
	
	public static function build($htmlObj,$config,$field){
		$fieldId = $config->getFieldId();
		
		//複数では無い場合
		if(is_array($field) && isset($field[-1])){
			$field = $field[-1];
		}
		
		if($config->getType() == "check"){
			
			$htmlObj->createAdd($fieldId, "SOYCMS_HTMLLabel",array(
				"list" => $field,
				"soy2prefix" => "cms",
			));
			
			$htmlObj->createAdd($fieldId . "_list", "SOYCMS_ObjectCustomFieldCheckListComponent",array(
				"list" => $field,
				"soy2prefix" => "cms",
				"config" => $config
			));
			return;
		}
		
		if(is_object($field)){
			
			if($field->getType() == "group"){
				
				$htmlObj->createAdd($fieldId, "_SOYCMS_ObjectCustomFieldGroupComponent",array(
					"config" => $config,
					"value" => $field,
					"childSoy2Prefix" => "cms"
				));
				
				return;
			}
			
			return self::_build($htmlObj,$field);
		}
		
		//複数の場合
		$htmlObj->createAdd($fieldId . "_list", "SOYCMS_ObjectCustomFieldListComponent",array(
			"list" => $field,
			"soy2prefix" => "cms",
			"config" => $config,
			"childSoy2Prefix" => "cms"
		));
		$sets_value = array();
		foreach($field as $field){
			$sets_value[] = $field->getValue();
		}
		$htmlObj->createAdd($fieldId . "_sets","SOYCMS_ObjectCustomFieldSetComponent",array(
			"soy2prefix" => "cms",
			"value" => implode(",",$sets_value)
		));
	}
	
	public static function _build($htmlObj,$field){
		$type = $field->getType();
		$value = $field->getValue();
		if(strlen($value)<1)$value = $field->getText();
		if($type == "input")$value = htmlspecialchars($value);
		if($type == "multi")$value = nl2br(htmlspecialchars($value));
		
		//画像のみ
		if($field->getType() == "image"){
			$array = $field->getValueObject();
			$src = @$array["src"];
			$title = @$array["title"];
			$alt = @$array["alt"];
			$value = htmlspecialchars($src,ENT_QUOTES);
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
		
		if($field->getType() == "check"){
			
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
	
	function setValue($value){
		$this->value = $value;
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

class SOYCMS_ObjectCustomFieldCheckListComponent extends HTMLList{
	private $config;
	
	function populateItem($entity){
		if(!$entity instanceof SOYCMS_ObjectCustomField){
			$entity = $this->config->getValueObject();
		}
		$this->addLabel("check_value",array(
			"soy2prefix" => "cms",
			"text" => $entity->getValue()
		));
	}
	
	function setConfig($config){
		$this->config = $config;
	}
}

class SOYCMS_HTMLLabel extends HTMLLabel{
	
	private $list = array();
	private $separate = ",";
	
	function setList($list){
		$this->list = $list;
	}
	
	function execute(){
		$tmp = array();
		foreach($this->list as $obj){
			$tmp[] = $obj->getValue();
		}
		$text = implode($this->separate,$tmp);
		$this->setText($text);
		
		parent::execute();
	}
	
	function setSeparate($value){
		$this->separate = $value;
	}
	
}

class _SOYCMS_ObjectCustomFieldGroupComponent extends SOYBodyComponentBase{
	
	private $config;
	private $value;
	
	
	function execute(){
		
		$field = $this->value;
		$config = $this->config;
		
		$_value = $field->getValueObject();
		$fields = $config->getFields();
		foreach($fields as $key => $_config){
			if(strlen($key)<1)continue;
			
			if($_config->getType() == "check"){
				$value = (isset($_value[$key])) ? $_value[$key] : array();
				if(is_object($value))$value = array($value);
				SOYCMS_ObjectCustomFieldHelper::build($this,$_config,$value);
				continue;
			}
			
			$value = (isset($_value[$key])) ? $_value[$key] : $_config->getValueObject();
			
			if(!$value instanceof SOYCMS_ObjectCustomField){
				$obj = new SOYCMS_ObjectCustomField();
				$obj->setType($_config->getType());
				$obj->setValue($value);
				$value = $obj;
			}
			
			if(is_object($value))$value->setFieldId($key);
			
			SOYCMS_ObjectCustomFieldHelper::_build($this,$value);
		
		}
		
		parent::execute();
	}
	
	

	public function getConfig(){
		return $this->config;
	}

	public function setConfig($config){
		$this->config = $config;
		return $this;
	}

	public function getValue(){
		return $this->value;
	}

	public function setValue($value){
		$this->value = $value;
		return $this;
	}
}
?>