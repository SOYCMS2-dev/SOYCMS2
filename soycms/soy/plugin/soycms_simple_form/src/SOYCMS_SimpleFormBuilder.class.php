<?php

class SOYCMS_SimpleFormBuilder {
	
	public static function getTypes(){
		return array(
			"input" => "１行テキスト(input)",
			"textarea" => "複数行テキスト(textarea)",
			"mailaddress" => "メールアドレス",
			"radio" => "ラジオボタン",
			"select" => "セレクトボックス",
			"checkbox" => "チェックボックス",
			"confirm" => "必須チェック(同意する)"
		);
	}
	
	function buildSubItems($content){
		$array = array();
		$lines = explode("\n",$content);
		foreach($lines as $key => $value){
			$value = trim($value);
			if(empty($value))continue;
			$array[] = $value;
		}
		
		return $array;
	}
	
	/**
	 *  投稿された値をテキストで返す
	 */
	function getValues() {
		$values = array();
		$texts = array();
		
		foreach($this->items as $key => $field){
			$id = $field->getId();
			$name = $id;
			$type = $field->getType();
			$option = $field->getOptions();
			
			$value = @$this->values[$key];
			$text = @$this->values[$key];
			
			$subitem = @$option["subitem"];
			
			if($subitem){
				$sep = @$option["separate"];
				if(!$sep)$sep = ",";
				$items = $this->buildSubItems($subitem);
				
				if(!is_array($value)){
					$value = (!is_null($value)) ? explode($sep,$value) : array();
				}
				
				$tmp = array();
				$tmpText = array();
				foreach($items as $_key => $item){
					if(in_array($_key,$value)){ /* キーで判定*/
						$tmp[] = $_key;
						$tmpText[] = $item;
					}
				}
				$value = implode($sep,$tmp);
				$text = implode($sep,$tmpText);
			}
			
			if($type == "confirm" && $value){
				$value = $field->getName();
			}
			
			$values[$key] = $value;
			$texts[$key] = $text;
		}
		
		$this->valuesTexts = $texts;
		
		return $values;
	}
	
	
	private $action;
	private $html;
	private $items = array();
	private $values = array();
	private $valuesTexts = array();
	
	private $isUserInputed = false;
	private $errors = array();
	
	function SOYCMS_SimpleFormBuilder($html = "",$values = null){
		$this->html = $html;
		
		if(empty($values) && !empty($_POST)){
			$this->values = $_POST;
			$this->isUserInputed = true;
		}
		
		if(!empty($values)){
			$this->isUserInputed = true;
			$this->values = $values;
		}
	}
	
	function getForm($html = "",$hiddenValues = array()){
		if(is_null($html))$html = $this->html;
		$webPage = SOY2HTMLFactory::createInstance("SOYCMS_SimpleForm_TemplatePage",array(
			"arguments" => array("simple_form_" . md5($html),$html),
			"values" => $this->values,
			"errors" => $this->errors,
			"action" => $this->action,
			"items" => $this->items,
			"builder" => $this,
			"mode" => (!empty($_POST)) ? "post" : "form"
		));
		
		$webPage->createAdd("contact_form","SOYCMS_SimpleForm_HTMLForm",array(
			"action" => $this->action,
			"hiddenValues" => $hiddenValues,
			"soy2prefix" => "cms"
		));
		
		
		$webPage->execute();
		$html = $webPage->getObject();
				
		return $html;
	}
	
	/**
	 * validationを行います。
	 */
	function validate($items,$values){
		
		foreach($items as $key => $field){
			$type = $field->getType();
			$value = @$values[$key];
			$option = $field->getOptions();
			
			if($field->getRequire()){
				
				//空の時
				if((is_array($value) && count($value) < 1) || (!is_array($value) && strlen($value) < 1)
				 || is_null($value)
				){
					$this->errors[$key] = "require";
					
				}
				
				if($type == "confirm" && !$value){
					$this->errors[$key] = "require";
				}
				
				if(@$option["confirm"] == 1){
					$confirmValue = @$values[$key . "_confirm"];
					if($field->getRequire() && empty($confirmValue)){
						$this->errors[$key . "_confirm"] = "require";
					}
				}
			}
			
			if(!empty($value)){
				switch($type){
					case "mailaddress":
						if(!$this->validateMailAddress($value)){
							$this->errors[$key] = "format";
						}
						if(@$option["confirm"] == 1 && !isset($this->errors[$key . "_confirm"])){
							if(@$values[$key . "_confirm"] != $value){
								$this->errors[$key . "_confirm"] = "confirm";
							}
							if(!$this->validateMailAddress(@$values[$key . "_confirm"])){
								$this->errors[$key . "_confirm"] = "format";
							}
						}
						break;
					case "input":
						if(@$option["validation"] != ""){
							$regex = null;
							
							switch($option["validation"]){
								case "alpha":
									$regex = "^[0-9a-zA-Z]+$";
									break;
								case "number":
									$regex = '/^[0-9]+$/';
									break;
								case "mailaddress":
									if(!$this->validateMailAddress($value)){
										$this->errors[$key] = "format";
									}
									break;
								case "regex":
									$regex = @$option["regex"];
									break;
							}
							if($regex && !@$this->validateRegex($value,$regex)){
								$this->errors[$key] = "format";
							}
						}
						break;
				}
				
				
			}
			
		}
		
		return empty($this->errors);
	}
	
	/**
	 * @return boolean
	 */
	function validateMailAddress($addr){
		$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
		$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
		$d3	 = '\d{1,3}';
		$ip	 = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
		$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";
		
		if(! preg_match('/'.$validEmail.'/i', $addr) ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return boolean
	 */
	function validateRegex($value,$regex){
		if(strlen($regex) < 1)return true;
		if($regex[0] != "/")$regex = "/" . preg_quote($regex) . "/";
		
		if(preg_match($regex,$value)){
			return true;
		}
		return false;
	}
	
	function convertValues($html,$isHTML = true){
		$values = $this->getValuesTexts();
		
		foreach($values as $key => $value){
			if($isHTML)$value = nl2br($value);
			$html = str_replace("#" . $key . "#", $value, $html);
		}
		
		$html = str_replace("#SITE_URL#", SOYCMS_SITE_URL, $html);
		return $html;
	}
	
	function getValuesTexts(){
		$this->getValues();
		return $this->valuesTexts;
	}
	
	function setValues($values){
		$this->values = $values;
	}
	
	function setAction($action){
		$this->action = $action;
	}
	
	function setItems($items){
		$this->items = $items;
	}

}

class SOYCMS_SimpleForm_HTMLForm extends HTMLForm{
	
	private $hiddenValues = array();
	
	function getStartTag(){
		$values = "";
		foreach($this->hiddenValues as $key => $value){
			$values .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />";
		}
		return parent::getStartTag() . $values;
	}
	

	function getHiddenValues() {
		return $this->hiddenValues;
	}
	function setHiddenValues($hiddenValues) {
		$this->hiddenValues = $hiddenValues;
	}
}

class SOYCMS_SimpleForm_TemplatePage extends HTMLTemplatePage{
	
	private $errors = array();
	private $values = array();
	private $items = array();
	private $builder;
	private $mode = "form";
	
	function execute(){
		$this->buildForm();
		$this->buildValues();
		$this->buildErrors();
	}
	
	function buildForm(){
		
		foreach($this->items as $key => $field){
			$id = $field->getId();
			$name = $id;
			$type = $field->getType();
			$option = $field->getOptions();
			
			$formValue = (empty($this->values[$key]) && $this->mode == "form") ? $field->getDefault() : @$this->values[$key];
			
			switch($type){
				case "textarea":
					$this->addTextArea("contact_" . $key,array(
						"name" => $key,
						"value" => $formValue,
						"soy2prefix" => "cms"
					));
					break;
				case "select":
					$items = $this->builder->buildSubItems(@$option["subitem"]);
					$selected = (is_numeric($formValue)) ? $formValue : array_search($formValue,$items);
					
					$this->addSelect("contact_" . $key,array(
						"name" => $key,
						"options" => $items,
						"selected" => $selected,
						"soy2prefix" => "cms",
						"indexOrder" => true
					));
					break;
				case "confirm":
					$this->addCheckbox("contact_" . $key,array(
						"elementId" => "contact_" . $key,
						"isBoolean" => true,
						"name" => $name,
						"value" => 1,
						"selected" => @$this->values[$key] == 1,
						"soy2prefix" => "cms"
					));
					break;
				case "checkbox":
					$name .= "[]";
				case "radio":
					$items = $this->builder->buildSubItems(@$option["subitem"]);
					$value = $formValue;
					
					if($type == "radio" && !is_numeric($value)){
						$value = array_search($value,$items);
					}
					
					if($type == "checkbox" && !is_array($value)){
						if(!is_null($value) && !empty($value)){
							$value = explode(",",$value);
							$tmp = array();
							foreach($value as $_value){
								if(is_numeric($_value)){
									$tmp[] = $_value;
								}else{
									$search = array_search($_value,$items);
									if($search !== false){
										$tmp[] = $search;
									}
								}
							}
							$value = $tmp;
						}else{
							$value = array();
						}
					}
					
					foreach($items as $_key => $item){
						$this->addCheckbox("contact_" . $key . "_" . $_key,array(
							"elementId" => "contact_" . $key . "_" . $_key,
							"name" => $name,
							"value" => $_key,
							"selected" => (is_array($value)) ? in_array($_key,$value) : $_key == $value,
							"soy2prefix" => "cms"
						));
					}
					$this->addModel("contact_" . $key,array(
						"soy2prefix" => "cms",
						"visible" => count($items) > 0
					));
					break;
				case "mailaddress":
					if(@$option["confirm"] == 1){
						$this->addInput("contact_" . $key . "_confirm",array(
							"name" => $key . "_confirm",
							"value" => @$this->values[$key . "_confirm"],
							"soy2prefix" => "cms"
						));
					}
				
				case "input":
					$this->addInput("contact_" . $key,array(
						"name" => $key,
						"value" => $formValue,
						"soy2prefix" => "cms"
					));
					break;
			}
		}
		
		$this->addInput("back_button",array(
			"name" => "back",
			"value" => "戻る",
			"soy2prefix" => "cms"
		));
	}
	
	function buildValues(){
		foreach($this->items as $key => $field){
			$id = $field->getId();
			$name = $id;
			$type = $field->getType();
			$option = $field->getOptions();
			
			$value = @$this->values[$key];
			$subitem = @$option["subitem"];
			
			if($subitem){
				$sep = @$option["separate"];
				if(!$sep)$sep = ",";
				$items = $this->builder->buildSubItems($subitem);
				if(!is_array($value)){
					$value = (!is_null($value)) ? array($value) : array();
				}
				$tmp = array();
				
				foreach($value as $_value){
					if(!isset($items[$_value]))continue;
					$tmp[] = $items[$_value];
				}
				$value = implode($sep,$tmp);
			}
			
			if($type == "textarea"){
				$value = nl2br(htmlspecialchars($value));
			}else{
				$value = htmlspecialchars($value);
			}
			
			if($type == "confirm"){
				$value = $field->getName();
			}
			
			
			$this->addLabel("contact_" . $key . "_text",array(
				"soy2prefix" => "cms",
				"html" => $value
			));
		}
		
		$this->addLink("site_link",array(
			"link" => SOYCMS_SITE_URL,
			"soy2prefix" => "cms"
		));
	}
	
	function buildErrors(){
		foreach($this->items as $key => $field){
			$value = @$this->errors[$key];
			$type = $field->getType();
			$option = $field->getOptions();
			
			if($field->getRequire()){
				$this->addModel($key . "_require_error",array(
					"soy2prefix" => "cms",
					"visible" => $value === "require"
				));
			}
			if($type == "input"){
				$this->addModel($key . "_format_error",array(
					"soy2prefix" => "cms",
					"visible" => $value === "format"
				));
			}
			
			if($type == "mailaddress" || $type == "input"){
				$this->addModel($key . "_format_error",array(
					"soy2prefix" => "cms",
					"visible" => $value === "format"
				));
			}
			
			//確認画面
			if($type == "mailaddress" && @$option["confirm"] == 1){
				$_value = @$this->errors[$key . "_confirm"];
				$this->addModel($key . "_confirm_error",array(
					"soy2prefix" => "cms",
					"visible" => $_value === "confirm"
				));
				$this->addModel($key . "_confirm_format_error",array(
					"soy2prefix" => "cms",
					"visible" => @$this->errors[$key . "_confirm"] === "format"
				));
				
				if($field->getRequire()){
					$this->addModel($key . "_confirm_require_error",array(
						"soy2prefix" => "cms",
						"visible" => @$this->errors[$key . "_confirm"] === "require"
					));
				}
			}
			
			$this->addModel($key . "_error",array(
				"soy2prefix" => "cms",
				"visible" => $value === true
			));
		}
	}
	
	/* getter setter */
	function setBuilder($obj){
		$this->builder = $obj;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	function getErrors() {
		return $this->errors;
	}
	function setErrors($errors) {
		$this->errors = $errors;
	}
	function setValues($values) {
		$this->values = $values;
	}
	function setItems($items){
		$this->items = $items;
	}
}
?>