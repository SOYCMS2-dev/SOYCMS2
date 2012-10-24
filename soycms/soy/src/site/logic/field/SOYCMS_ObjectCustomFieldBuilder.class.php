<?php

class SOYCMS_ObjectCustomFieldBuilder{
	
	/**
	 * 準備
	 * @param string $formId
	 * @param string $formName
	 * @param string $type(option) if you make the form for users(not administrators), you might set this value "user"
	 */
	public static function prepare($formId,$formName,$type = "entry"){
		return new SOYCMS_ObjectCustomFieldBuilder($formId,$formName,$type);
	}
	
	private function SOYCMS_ObjectCustomFieldBuilder($formId,$formName,$type){
		$this->formId = $formId;
		$this->formName = $formName;
		$this->type = $type;
	}
	
	private $formId;
	private $formName;
	private $type = "label";
	private $_functions = array();
	private $_filterFunctions = array();
	
	/**
	 * @param SOYCMS_CustomFieldConfig $config
	 * @param SOYCMS_CustomField $value
	 */
	public function buildForm($config,$value = null){
		
		$formId = $this->formId;
		$formName = $this->formName;
		
		$nameAttribute = $formName . "[".$config->getFieldId()."]";
		$classAttribute = $formId . "-" . $config->getFieldId();
		$prefix = "custom-";
		$type = $config->getType();
		
		if($config->isMulti()){
			$nameAttribute .= "[%INDEX%]";
		}
		
		//複数チェックの場合
		if($type == "check"){
			$value = array($value);
		}
		
		$_html = "";	//全体を囲む
		if(isset($this->_functions[$config->getFieldId()])){
			$_html .= call_user_func_array($this->_functions[$config->getFieldId()], array($this,$config,$value));
		}else if(isset($this->_filterFunctions[$type])){
			$_html .= call_user_func_array($this->_filterFunctions[$type],array($this,$config,$value));
		}else{
			if(!is_array($value)){
				$value = array($value);
			}
			foreach($value as $key => $_value){
				$_html .= self::buildFormHTML($type,array(
							"name" => $nameAttribute,
							"label" => $config->getLabel(),
							"class" => $classAttribute,
							"id" => $classAttribute . "-" . $key,
							"prefix" => $prefix,
							"key" => $key,
							"fields" =>$config->getFields(),
							"multi" => $config->isMulti(),
							"defaultValue" => $config->getDefaultValue(),
							"option" => $config->getOption(),
							"options" => $config->getOptionsArray()
				),$_value);
			}
			
			if($config->isMulti()){
				$_html .= "\n" . $this->buildFormHTML($type,array(
							"name" => $nameAttribute,
							"label" => $config->getLabel(),
							"class" => "field-template",
							"id" => $classAttribute . "-#INDEX#",
							"prefix" => $prefix,
							"key" => "#INDEX#",
							"fields" => $config->getFields(),
							"multi" => false,
							"defaultValue" => $config->getDefaultValue(),
							"option" => $config->getOption(),
							"options" => $config->getOptionsArray()
				));
				$_html .= '<p class="field-operation-append-btn"><a href="javascript:void(0);" class="s-btn field-append-btn" onclick="FieldContoller.append(this,'.(int)$config->getMultiMax().');" ><em>＋</em></a></p>';
			}
		}
		
		$body = '<div class="field-section">' . $_html . '</div>';
		
		return $body;
		
	}
	
	/**
	 * フォームのHTMLを作成する
	 * @param string $type
	 * @param Array $config
	 * @param SOYCMS_CustomField $value
	 */
	function buildFormHTML($type,$config,$value = null){
		$nameAttribute = $config["name"];
		$label = @$config["label"];
		$key = @$config["key"];
		$idAttribute = @$config["id"];
		$classAttribute = @$config["class"];
		$prefix  = @$config["prefix"];
		$multi = @$config["multi"];
		$option = @$config["option"];
		$options = @$config["options"];
		$isWrap = (!isset($config["wrap"])) ? true : $config["wrap"];
		
		$h_formName = str_replace("%INDEX%",$key,$nameAttribute);
		$h_text = ($value && is_object($value)) ? htmlspecialchars($value->getText(),ENT_QUOTES) : null;
		$h_value = ($value && is_object($value)) ? htmlspecialchars($value->getValue(),ENT_QUOTES) : null;
		
		switch($type){
			case "group":
				$fields = $config["fields"];
				$_value = (is_object($value)) ? $value->getValueObject() : array();
				
				$oldFormName = $this->formName;
				$this->formName = $h_formName;
				
				$body = "<div class=\"field-child-list\">";
				foreach($fields as $key => $_config){
					if(strlen($key)<1)continue;
					$description = $_config->getDescription();
						
					$body .= "<div class=\"field-child\">";
					$body .= "<h5>" . $_config->getLabel() . "</h5>";
					if(strlen($description)>0)$body .= "<p>" . nl2br($description) . "</p>";
					$body .= $this->buildForm($_config,@$_value[$key]);
					$body .= "</div>";
				}
				$body .= "</div>";
				
				$this->formName = $oldFormName;
				break;
			case "checkbox":
		
				//DefaultValueがあればそれを使う
				$checkbox_value = (strlen($config["defaultValue"]) >0) ? $config["defaultValue"] : $label ;
				$h_checkbox_value = htmlspecialchars($checkbox_value,ENT_QUOTES,"UTF-8");
		
				$body = '<input type="checkbox" class="'.$prefix.'checkbox"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'"'
				.' value="'.$h_checkbox_value.'"'
				.( ($h_value == $checkbox_value) ? ' checked="checked"' : ""  )
				.' />' .
							' <label for="'.$idAttribute.'">'.$label.'</label>';
		
				break;
			case "radio":
				$value = (is_null($h_value)) ? $config["defaultValue"] : $h_value ;
		
				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option)>0){
						if($option[0] == "*"){
							$option = substr($option,1);
							if(!$h_value)$h_value = $option;
						}
						$h_option = htmlspecialchars($option,ENT_QUOTES,"UTF-8");
						$id = 'custom_field_radio_'.$this->getFormId().'_'.$key;
		
						$body .= '<input type="radio" class="'.$prefix.'radio"' .
										 ' name="'.$h_formName.'"' .
										 ' id="'.$id.'"'.
										 ' value="'.$h_option.'"' .
						(($option == $h_value) ? ' checked="checked"' : "") .
										 ' />';
						$body .= '<label for="'.$id.'">'.$h_option.'</label>';
					}
				}
		
				break;
			case "check":
				$selectedValue = array();
				//初期値
				if(!is_array($value)){
					$selectedValue = explode(",",$config["defaultValue"]);
					//保存されている場合
				}else{
					foreach($value as $_value){
						if(!$_value)continue;
						if(!is_object($_value))continue;
						$selectedValue[] = $_value->getValue();
					}
				}
		
				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option)>0){
						$h_option = htmlspecialchars($option,ENT_QUOTES,"UTF-8");
						$id = 'custom_field_check_'.$idAttribute . "_" . $this->formId.'_'.$key;
						$body .= '<input type="checkbox" class="'.$prefix.'check"' .
										 ' name="'.$h_formName.'[]"' .
										 ' id="'.$id.'"'.
										 ' value="'.$h_option.'"' .
						((in_array($option,$selectedValue)) ? ' checked="checked"' : "") .
										 ' />';
						$body .= '<label for="'.$id.'">'.$h_option.'</label> ';
					}
				}
				break;
		
			case "select":
				if(is_object($value))$value = $value->getValue();
				$value = (!$value) ? $config["defaultValue"] : $value;
				
				$body = '<select class="'.$prefix.'select" name="'.$h_formName.'" id="'.$idAttribute.'">';
				if(!$config["defaultValue"])$body .= '<option value="">----</option>';
				foreach($options as $key => $_option){
					$_option = trim($_option);
					if(strlen($_option)>0){
						$h_option = htmlspecialchars($_option,ENT_QUOTES,"UTF-8");
						$body .= '<option value="'.$key.'" ' .
						(($key == $value) ? 'selected="selected"' : "") .
										 '>' . $h_option . '</option>' . "\n";
					}
				}
				$body .= '</select>';
		
				break;
			case "multi":
				$h_value = (empty($h_value)) ? $config["defaultValue"] : $h_value ;
					
				$body = '<textarea class="textarea m-area liq-area resizable"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'"'
				.' placeholder="'.$label.'">'
				.$h_value.'</textarea>';
				break;
			case "image":
				if(!$value || !($value instanceof SOYCMS_ObjectCustomField)){
					$value = new SOYCMS_ObjectCustomField();
				}
				$h_value = $value->getValueObject();
		
				$body = '<dl class="form-item"><dt>画像のURL</dt><dd><input type="text" size="50" class="s-area image-input"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'[src]"'
				.' value="'.@$h_value["src"].'" '
				.' '
				.' /></dd>';
		
				if($this->type != "user"){
					/* $body .= '<dt>タイトル属性</dt><dd><input type="text" class="s-area"'
								.' id="'.$idAttribute.'"'
								.' name="'.$h_formName.'[title]"'
								.' value="'.@$h_value["title"].'" '
								.' size="40" '
								.' /></dd>';
					*/
					$body .= '<dt><nobr>代替テキスト属性(alt)</nobr></dt>';
					$body .= '<dd><input type="text" class="s-area"'
								.' id="'.$idAttribute.'"'
								.' name="'.$h_formName.'[alt]"'
								.' value="'.@$h_value["alt"].'" '
								.' size="40" '
								. ' /></dd>';
				}
		
		
				$body .=((strlen(@$h_value["src"])>0) ? '<dd><br /><img id="'.$idAttribute.'_img" class="thumb-210" src="'.htmlspecialchars($h_value["src"],ENT_QUOTES).'" /></dd>' : "") ;
				$body .= "</dl>";
		
				break;
			case "file":
		
				$body = '<span><input type="text" class="s-area"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'"'
				.' value="'.$h_value.'" '
				.' size="40" '
				.' /></span> ';
				$body .= '<span><input type="button" class="s-btn" value="参照" onclick="aobata_editor.show_attachments(function(img,link){$(\'#'.$idAttribute.'\').val(img);});" /></span>';
					
				break;
			case "static":
				$body = '<input type="hidden" class="s-area liq-area"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'"'
				.' value="'.$h_value.'"'
				.' />';
				$body .= "<span>" . $h_value . "</span>";
		
				break;
			case "wysiwyg":
			case "html":
				$h_value = (empty($h_value)) ? $config["defaultValue"] : $h_value ;
				$class = "aobata_editor";
				if($type == "html"){
					$class = "aobata_preview";
				}
		
				$html = array();
		
				$html[] = '<div class="section_list">
							<div class="article-header">
								<div class="text-mode-panel">
									<div class="panel">
										<div class="panel-line">
											<div class="panel-parts"><a href="javascript:void(0);" onclick="aobata_editor.find(this).swapMode();" class="icon-btn btn-html toggle-btn" title="HTML"><em>HTML</em></a></div>
										</div>
									</div>
								</div>
								<div class="wysiwyg-mode-panel no-sections"></div>
							</div>
			
							<div class="article-body">
								<textarea id="'.$idAttribute.'" name="'.$h_formName.'" class="m-area liq-area '.$class.'">'.$h_value.'</textarea>
							</div>
							<div class="article-footer">
								<div class="panel">
									<div class="panel-line">
										<div class="panel-parts close-editor" style="float:right;"><a class="icon-btn btn-close" href="javascript:void(0);" title="閉じる"><em>閉じる</em></a></div>
									</div>
								</div>
							</div>
						</div><!-- // .section_list -->';
		
				$body = implode("\n",$html);
				break;
			case "date":
			case "datetime":
				if(!is_numeric($h_value))$h_value = null;
				$date = (strlen($h_value)>0) ? date("Y-m-d",$h_value) : "";
				$time = (strlen($h_value)>0) ? date("H:i",$h_value) : "";
					
				$html = array();
				$html[] = '<input type="text" class="m-area date-input" size="11" name="'.$h_formName.'[0]" value="'.$date.'" />';
				if($type == "datetime"){
					$html[] = '@<input type="text" class="m-area time-input" size="9" name="'.$h_formName.'[1]" value="'.$time.'" />';
				}
				$body = implode("",$html);
				break;
			case "time":
				if(!is_numeric($h_value))$h_value = null;
				$time = (strlen($h_value)>0) ? date("H:i",$h_value) : "";
					
				$html = array();
				$html[] = '<input placeholder="00:00" type="text" class="m-area time-input" size="9" name="'.$h_formName.'[0]" value="'.$time.'" />';
				$body = implode("",$html);
				break;
			case "url":
				if(!$value || !($value instanceof SOYCMS_ObjectCustomField)){
					$value = new SOYCMS_ObjectCustomField();
				}
				$h_value = $value->getValueObject();
		
				$body = '<dl class="form-item"><dt>リンク先URL</dt><dd><input type="text" class="s-area liq-area"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'[href]"'
				.' value="'.@$h_value["href"].'" '
				.' '
				.' /></dd>' .
							   '<dt>タイトル(title)</dt><dd><input type="text" class="s-area"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'[title]"'
				.' value="'.@$h_value["title"].'" '
				.' size="40" '
				.' /></dd>' .
							   	'</dl>';
		
				break;
			case "input":
			default:
				$class = "m-area liq-area";
					
				if($type == "number"){
					$class = "m-area rule-number";
				}
				if($type == "alphabet"){
					$class = "m-area liq-area rule-alpha";
				}
					
				$body = '<input type="text" class="'.$class.'"'
				.' id="'.$idAttribute.'"'
				.' name="'.$h_formName.'"'
				.' value="'.$h_value.'"'
				.' placeholder="'.$label.'"/>';
				break;
		}
		
		$buttons = '<a class="s-btn field-remove-btn"><em>ー</em></a>';
		$buttons .= '<a class="s-btn field-cancel-btn"><em>Cancel</em></a>';
		if($multi){
			$body .= "<p class=\"field-operation-btn\">" . $buttons . "</p>";
		}
		
		
		if($isWrap){
			$_html = '<div class="field-form '.$classAttribute.'">' . $body . '</div>';
		}else{
			$_html = $body;
		}
			
		return $_html;
	}
	
	function setFilter($type, $function){
		$this->_filterFunctions[$type] = $function;
	}
	
	function setBuilder($id,$function){
		$this->_functions[$id] = $function;
	}

	private function getFormId(){
		return $this->formId;
	}

	private function setFormId($formId){
		$this->formId = $formId;
		return $this;
	}
}