<?php
/**
 * @table soycms_site_object_field
 */
class SOYCMS_ObjectCustomField extends SOY2DAO_EntityBase{
	
	public static function getValues($object,$objectId){
		$dao = SOY2DAOFactory::create("SOYCMS_ObjectCustomFieldDAO");
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
		$dao = SOY2DAOFactory::create("SOYCMS_ObjectCustomFieldDAO");
		$dao->deleteByParams($object,$objectId);
		
		foreach($values as $key => $value){
			if(!isset($configs[$key]))continue;
			
			$obj = new SOYCMS_ObjectCustomField();
			$obj->setFieldId($key);
			$obj->setObject($object);
			$obj->setObjectId($objectId);
			$obj->setType($configs[$key]->getType());
			
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
			}else{
				$obj->setValue($value);
				$obj->setText($value);
				$dao->insert($obj);
			}
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
		if(($this->type == "date" || $this->type == "datetime" || $this->type == "time") && is_array($value)){
			$value = implode(" ",$value);
			if(strlen($value) > 1){
				$value = strtotime($value);
			}else{
				$value = null;
			}
		}
		
		if($this->type == "group" && is_array($value)){
			foreach($value as $key => $_value){
				$_valueObj = new SOYCMS_ObjectCustomField();
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
		return $this->text;
	}
	function setText($text) {
		if(is_array($text)){
			$text = soy2_serialize($text);
		}
		$this->text = $text;
	}
	
	function getValueObject(){
		return soy2_unserialize($this->getValue());
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
	 * @query #object# = :object AND #objectId# = :objectId
	 */
	abstract function getByParams($object,$objectId);
	
} 

class SOYCMS_ObjectCustomFieldConfig{
	
	/**
	 * 設定を取得
	 */
	public static function loadConfig($type){
		$dir = self::getConfigDirectory();
		$filepath = $dir . $type . ".ini";
		
		if(file_exists($filepath)){
			$res = soy2_unserialize(file_get_contents($filepath));
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
		$filepath = $dir . $type . ".ini";
		
		$res = array();
		foreach($configs as $config){
			$res[$config->getFieldId()] = $config;
		}
		
		file_put_contents($filepath, soy2_serialize($res));
	}
	
	public static function getConfigDirectory(){
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
		$types["wysiwyg"] = "HTML";
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
			"checkbox" => "チェックボックス",
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
	
	function getDefaultValue(){
		return @$this->config["defaultValue"];
	}
	function getRequire(){
		return (boolean)@$this->config["require"];
	}
	function getOption(){
		return @$this->config["option"];
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
		$this->config = $config;
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