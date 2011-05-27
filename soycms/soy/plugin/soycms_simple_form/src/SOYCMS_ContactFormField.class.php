<?php

class SOYCMS_ContactFormField{
	
	public $id;
	public $name;
	public $type;
	public $label; //placeholder
	public $require = false; //必須チェックが必要か
	public $default = "";	  //初期値
	public $options = array();
	
	function getTypeText(){
		$array = SOYCMS_SimpleFormBuilder::getTypes();
		return @$array[$this->type];
	}
	
	function getValidations(){
		return array(
			"number" => "数値のみ",
			"alpha" => "半角英数",
			"mailaddress" => "メールアドレス",
			"regex" => "正規表現"
		);
	}
	
	function setOption($key,$val){
		$this->options[$key] = $val;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getRequire() {
		if($this->type == "confirm")return true;
		return $this->require;
	}
	function setRequire($require) {
		$this->require = $require;
	}
	function getDefault() {
		return $this->default;
	}
	function setDefault($default) {
		$this->default = $default;
	}
	function getOptions() {
		return $this->options;
	}
	function setOptions($options) {
		$this->options = $options;
	}
}
