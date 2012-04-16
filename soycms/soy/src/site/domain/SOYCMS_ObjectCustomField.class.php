<?php
/**
 * @table soycms_site_object_field
 */
class SOYCMS_ObjectCustomField extends SOY2DAO_EntityBase{
	
	public static function getValues($object,$objectId){
		$dao = SOY2DAOContainer::get("SOYCMS_ObjectCustomFieldDAO");
		$values = $dao->getByParams($object,$objectId);
		$res = array();
		foreach($values as $value){
			$fieldId = $value->getFieldId();
			if(!isset($res[$fieldId]))$res[$fieldId] = array();
			if($value->getIndex() == -1){
				$res[$fieldId] = $value;
			}else{
				$res[$fieldId][$value->getIndex()] = $value;
			}
		}
		
		return $res;
	}
	
	public static function setValues($object,$objectId,$values){
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig($object);
		self::_setValues($object,$objectId,$values,$configs);
	}
	public static function setObjectValues($object,$objectId,$values,$option = null){
		$configs = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($object);
		if($option){
			$configs = array_merge($configs,SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($option));
		}
		self::_setValues($object,$objectId,$values,$configs);
	}
	
	private static function _setValues($object,$objectId,$values,$configs){
		$dao = SOY2DAOContainer::get("SOYCMS_ObjectCustomFieldDAO");
		$dao->deleteByParams($object,$objectId);
		
		foreach($values as $key => $value){
			if(!isset($configs[$key])){
				continue;
			}
			
			$type = $configs[$key]->getType();
			
			$obj = new SOYCMS_ObjectCustomField();
			$obj->setFieldId($key);
			$obj->setObject($object);
			$obj->setObjectId($objectId);
			$obj->setType($type);
			
			if($configs[$key]->isMulti()){
				$counter = 0;
				foreach($value as $_index => $_value){
					if(!is_numeric($_index))continue;
					$obj->setIndex($counter);
					$obj->setValue($_value);
					$obj->setText($_value);
					$dao->insert($obj);
					$counter++;
				}
			}else if($type == "check"){
				$counter = 0;
				if(is_array($value)){
					foreach($value as $_index => $_value){
						if(!is_numeric($_index))continue;
						$obj->setIndex($counter);
						$obj->setValue($_value);
						$obj->setText($_value);
						$dao->insert($obj);
						$counter++;
					}
				}
			}else if($type == "input"){
				if(is_array($value))$value = implode("-",$value);
				$obj->setValue($value);
				$obj->setText($value);
				$dao->insert($obj);
			}else{
				$obj->setValue($value);
				$obj->setText($value);
				$dao->insert($obj);
			}
		}
	}
	
	/**
	 * @return boolean
	 */
	function isEmpty(){
		$tmp = $this->getValueObject();
		if(is_array($tmp)){
			$empty = true;
			foreach($tmp as $_value){
				if(is_object($_value)){
					$empty &= $_value->isEmpty();
				}else{
					$empty &= empty($_value);
				}
			}
			return $empty;
		}
		
		return empty($this->value);
	}
	
	function toString(){
		switch($this->type){
			case "date":
				return (is_numeric($this->value)) ? date("Y-m-d",$this->value) : null;
			case "datetime":
				return (is_numeric($this->value)) ? date("Y-m-d H:i",$this->value) : null;
			case "group":
				$res = array();
				$values = $this->getValueObject();
				foreach($values as $value){
					$res[] = $value->toString();
				}
				return implode(" ",$res);
				break;
			default:
				return $this->getText();
		}
	}
	
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column field_id
	 */
	private $fieldId;
	
	/**
	 * @column object_id
	 */
	private $objectId;
	
	private $object = "label";
	
	/**
	 * @column field_type
	 */
	private $type;
	
	/**
	 * @column field_index
	 */
	private $index = -1;
	
	/**
	 * @column object_value
	 */
	private $value;
	
	/**
	 * @column object_text
	 */
	private $text;
	
	/* getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getObjectId() {
		return $this->objectId;
	}
	function setObjectId($objectId) {
		$this->objectId = $objectId;
	}
	function getObject() {
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		if($value instanceof SOYCMS_ObjectCustomField){
			$value = $value->getValue();
		}
		
		if($this->type == "date" && is_array($value)){
			$_value = implode("-",$value);
			if(preg_match("/^\d+-\d+-\d+$/",$_value)){
				$value = $_value;
				$this->setText($value);
				$value = strtotime($value);
			}
		}
		
		if(($this->type == "date" || $this->type == "datetime" || $this->type == "time") && is_array($value)){
			if(is_array($value[0]))$value[0] = implode(":",$value[0]);
			$value = implode(" ",$value);
			if(strlen($value) > 1){
				$this->setText($value);
				$value = strtotime($value);
			}else{
				$value = null;
			}
		}
		
		if($this->type == "group" && is_array($value)){
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("_" . $this->getFieldId());
			foreach($value as $key => $_value){
				$config = @$fields[$key];
				if(!$config)continue;
				
				if($config->getType() == "check"){
					$array = array();
					$counter = 0;
					foreach($_value as $_index => $selectedValue){
						if(!is_numeric($_index))continue;
						$obj = clone($this);
						$obj->setIndex($counter);
						$obj->setValue($selectedValue);
						$obj->setText($selectedValue);
						$array[] = $obj;
						$counter++;
					}
					$value[$key] = $array;
					continue;
				}
				
				
				$_valueObj = new SOYCMS_ObjectCustomField();
				$_valueObj->setType($config->getType());
				$_valueObj->setValue($_value);
				$value[$key] = $_valueObj;
			}
		}
		
		if(is_array($value)){
			$value = soy2_serialize($value);
		}
		$this->value = $value;
	}
	function getText() {
		if(strlen($this->text) < 1)return $this->value;
		return $this->text;
	}
	function setText($text) {
		if($text instanceof SOYCMS_ObjectCustomField){
			$text = $text->getValue();
		}
		
		if(is_array($text)){
			$text = soy2_serialize($text);
		}
		$this->text = $text;
	}
	
	function getValueObject(){
		$res = @soy2_unserialize($this->getValue());
		if($res === false){
			return null;
		}
		
		return $res;
	}
	
	function __toString(){
		return $this->getText();
	}

	function getIndex() {
		return $this->index;
	}
	function setIndex($index) {
		$this->index = $index;
	}
}

/**
 * @entiry SOYCMS_ObjectCustomField
 */
abstract class SOYCMS_ObjectCustomFieldDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_ObjectCustomField $obj);
	abstract function update(SOYCMS_ObjectCustomField $obj);
	abstract function delete($id);
	
	/**
	 * @index fieldId
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function deleteByParams($object,$objectId);
	
	abstract function get();
	
	/**
	 * @final
	 */
	function getByParams($object,$objectId,$fieldId = null){
		if($fieldId){
			return $this->getObjectByParams($object,$objectId,$fieldId);
		}else{
			return $this->getByParamsImpl($object,$objectId);
		}
	}
	
	/**
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function getByParamsImpl($object,$objectId);
	
	/**
	 * @return object
	 * @query #object# = :object AND #objectId# = :objectId AND #fieldId# = :fieldId
	 */
	abstract function getObjectByParams($object,$objectId,$fieldId);
	
}

class SOYCMS_ObjectCustomFieldConfig{
	
	/**
	 * 設定を取得
	 */
	public static function loadConfig($type){
		$dir = self::getConfigDirectory();
		$filepath = $dir . str_replace("/","-",$type);
		
		if(file_exists($filepath . ".json")){
			$res = json_decode(file_get_contents($filepath . ".json"));
			if($res){
				$result = array();
				foreach($res as $key => $array){
					$result[$key] = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$array);
				}
				return $result;
			}
		}
		
		if(file_exists($filepath . ".ini")){
			$res = soy2_unserialize(file_get_contents($filepath . ".ini"));
			if($res && is_array($res)){
				return $res;
			}
		}
		return array();
	}
	
	/**
	 * 個別設定を取得
	 */
	public static function loadObjectConfig($type){
		$common = self::loadConfig("common");
		$res = self::loadConfig($type);
		
		foreach($res as $key => $field){
			if(!isset($common[$key])){
				unset($res[$key]);
				continue;
			}
			$res[$key] = $common[$key];
		}
		
		return $res;
	}
	
	/**
	 * 設定を保存
	 */
	public static function saveConfig($type,$configs){
		$dir = self::getConfigDirectory();
		$filepath = $dir . str_replace("/","-",$type) . ".json"; /* 20110725 jsonに変更 */
		
		$res = array();
		foreach($configs as $config){
			$fieldId = $config->getFieldId();
			$fieldId = str_replace("-","_",$fieldId);
			$config->setFieldId($fieldId);
			
			$res[$fieldId] = SOY2::cast("object",$config);
		}
		
		file_put_contents($filepath, json_encode($res));
	}
	
	public static function getConfigDirectory(){
		
		if(SOYCMSConfigUtil::get("field_dir")){
			return SOYCMSConfigUtil::get("field_dir");
		}
		
		$dir = SOYCMS_SITE_DIRECTORY . ".field/";
		if(!file_exists($dir)){
			mkdir($dir,0755);
		}
		return $dir;
	}
	
	/**
	 * 項目
	 */
	public static function getTypes(){
		$types = self::getChildTypes();
		$types["group"] = "グループ";
		$types["wysiwyg"] = "HTML(WYSIWYGエディタ)";
		$types["html"] = "HTML";
		return $types;
	}
	
	public static function getRegisteredTypes(){
		
		$args = func_get_args();
		$ignores = array();
		foreach($args as $arg){
			if(is_array($arg)){
				$ignores = array_merge($ignores,$arg);
			}
		}
		
		$common = self::loadConfig("common");
		$types = self::getTypes();
		$res = array();
		foreach($common as $key => $value){
			if(in_array($key,$ignores))continue;
			$res[$key] = "[" . $types[$value->getType()].  "]" . $value->getName() . "(".$value->getFieldId().")";
		}
		
		return $res;
	}
	
	public static function getChildTypes(){
		return array(
			"input" => "テキスト",
			"multi" =>"複数行テキスト",
			"number" => "数値",
			"alphabet" => "半角英数字",
			"checkbox" => "チェックボックス",
			"check" => "チェックボックス(複数)",
			"radio" => "ラジオボタン",
			"select" => "セレクトボックス",
			"url" => "URL",
			"date" => "日付",
			"datetime" => "日付と時刻",
			"time" => "時刻",
			"file" => "ファイル",
			"image" => "画像",
			"static" => "固定値",
		);
	}
	
	/**
	 * 子要素を追加
	 */
	function getFields(){
		if($this->type != "group")return array();
		$configs = self::loadConfig("_" . $this->getFieldId());
		
		if(empty($configs)){
			$obj = new SOYCMS_ObjectCustomFieldConfig();
			$obj->setFieldId("description");
			$obj->setType("multi");
			$obj->setName($this->getName() . "の説明");
			$configs["description"] = $obj;
		}
		
		return $configs;
	}
	
	function setFields($configs){
		if($this->type != "group")return array();
		
		foreach($configs as $key => $config){
			if(empty($key))unset($configs[$key]);
		}
		self::saveConfig("_" . $this->getFieldId(),$configs);
	}
	
	function getValueObject(){
		$obj = new SOYCMS_ObjectCustomField();
		$obj->setFieldId($this->getFieldId());
		$obj->setType($this->getType());
		return $obj;
	}
	
	private $fieldId;
	private $name = "[new label]";
	private $label = "";
	private $order;
	private $type;
	private $option;
	private $config;
	private $editable = true;
	private $multi = false;
	private $multiMax = "";
	
	function isMulti(){
		return (boolean)$this->multi;
	}
	
	function getTemplate(){
		return @$this->config["template"];
	}
	function getDefaultValue(){
		return @$this->config["defaultValue"];
	}
	function getRequire(){
		return (boolean)@$this->config["require"];
	}
	function getOption(){
		return @$this->config["option"];
	}
	
	function getOptionsArray(){
		$res = array();
		$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->getOption()));
		foreach($options as $key => $value){
			$value = trim($value);
			if(strlen($value) < 1)continue;
			if(strpos($value,":")){
				list($key,$value) = explode(":",$value);
			}
			
			$res[$key] = $value;
		}
		
		return $res;
	}
	
	function getDescription(){
		return @$this->config["description"];
	}
	
	/* getter setter */

	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getLabel() {
		if(strlen($this->label)<1)return $this->getName();
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = (array)$config;
	}

	function getEditable() {
		return $this->editable;
	}
	function setEditable($editable) {
		$this->editable = $editable;
	}

	function setDescription($description) {
		$config = $this->getConfig();
		$config["description"] = $description;
		$this->setConfig($config);
	}

	function getName() {
		if(strlen($this->name)<1)return $this->getFieldId();
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}

	function getMulti() {
		return $this->multi;
	}
	function setMulti($multi) {
		$this->multi = $multi;
	}

	function getMultiMax() {
		return $this->multiMax;
	}
	function setMultiMax($multiMax) {
		$this->multiMax = $multiMax;
	}
}
?>