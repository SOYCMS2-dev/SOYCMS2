<?php

class CustomFieldList extends HTMLList{
	
	private $type = "label";
	private $objectId;
	private $formName = "ObjectCustomField";
	private $formId = "object_custom_field";
	private $values = array();

	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getObjectId() {
		return $this->objectId;
	}
	function setObjectId($objectId) {
		$this->objectId = $objectId;
	}
	function getFormName() {
		return $this->formName;
	}
	function setFormName($formName) {
		$this->formName = $formName;
	}
	function getValues() {
		return $this->values;
	}
	function setValues($values) {
		$this->values = $values;
	}

	function getFormId() {
		return $this->formId;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}

	function init(){
		$this->values = SOYCMS_ObjectCustomField::getValues($this->type,$this->objectId);
	}
	
	function populateItem($entity,$key){
		
		$this->addModel("field_wrap",array(
			"attr:id" => "field-" . $entity->getFieldId()
		));
		
		$this->addLabel("field_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("field_description",array(
			"text" => $entity->getDescription()
		));
		
		$this->addLabel("field_label",array(
			"text" => $entity->getLabel()
		));
		$this->addLabel("field_id",array(
			"text" => $entity->getFieldId()
		));
		
		$this->addLabel("field_form",array(
			"html" => $this->getForm($this->getFormName(), $entity,@$this->values[$entity->getFieldId()])
		));
		
		$this->addModel("is_multi",array(
			"visible" => $entity->isMulti()
		));
		
		
	}
	
	/**
	 * フォーム部分のHTMLを取得
	 */
	function getForm($formName,$config,$_value){
		$nameAttribute = $formName . "[".$config->getFieldId()."]";
		$classAttribute = $this->getFormId() . "-" . $config->getFieldId();
		$prefix = "custom-";
		
		if($config->isMulti()){
			$nameAttribute .= "[%INDEX%]";
		}
		
		if(!is_array($_value)){
			$_value = array($_value);
		}
		
		$_html = "";	//全体を囲む
		$type = $config->getType();
		
		foreach($_value as $key => $value){
			$_html .= $this->getFormHTML($type,array(
				"name" => $nameAttribute,
				"label" => $config->getLabel(),
				"class" => $classAttribute,
				"id" => $classAttribute . "-" . $key,
				"prefix" => $prefix,
				"key" => $key,
				"fields" =>$config->getFields(),
				"multi" => $config->isMulti(),
				"defaultValue" => $config->getDefaultValue(),
				"option" => $config->getOption()
			),$value);
		}
		
		if($config->isMulti()){
			$_html .= $this->getFormHTML($type,array(
				"name" => $nameAttribute,
				"label" => $config->getLabel(),
				"class" => "field-template",
				"id" => $classAttribute . "-#INDEX#",
				"prefix" => $prefix,
				"key" => "#INDEX#",
				"fields" => $config->getFields(),
				"multi" => false,
				"option" => $config->getOption(),
			));
			
			$_html .= '<p class="field-operation-append-btn"><a href="javascript:void(0);" class="s-btn field-append-btn"><em>＋</em></a></p>';
		}
		
		$body = '<div class="field-section">' . $_html . '</div>';
		
		return $body;
	}
	
	function getFormHTML($type,$config,$value = null){
		$nameAttribute = $config["name"];
		$label = $config["label"];
		$key = $config["key"];
		$idAttribute = $config["id"];
		$classAttribute = $config["class"];
		$prefix  = $config["prefix"]; 
		$multi = $config["multi"];
		$option = $config["option"];
		
		$h_formName = str_replace("%INDEX%",$key,$nameAttribute);
		$h_text = ($value && is_object($value)) ? htmlspecialchars($value->getText(),ENT_QUOTES) : null;
		$h_value = ($value && is_object($value)) ? htmlspecialchars($value->getValue(),ENT_QUOTES) : null;
		
		switch($type){
			case "group":
				$fields = $config["fields"];
				$_value = (is_object($value)) ? $value->getValueObject() : array();
				
				$body = "<div class=\"field-child-list\">";
				foreach($fields as $key => $_config){
					if(strlen($key)<1)continue;
					$body .= "<div class=\"field-child\">";
					$body .= "<h5>" . $_config->getLabel() . "</h5>";
					$body .= $this->getForm($h_formName,$_config,array(@$_value[$key]));
					$body .= "</div>";
				}
				$body .= "</div>";
				
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
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$option));
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
			case "select":
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$option));
				$value = (is_null($h_value)) ? $config["defaultValue"] : $h_value ;
				
				$body = '<select class="'.$prefix.'select" name="'.$h_formName.'" id="'.$idAttribute.'">';
				$body .= '<option value="">----</option>';
				foreach($options as $_option){
					$_option = trim($_option);
					if(strlen($_option)>0){
						$h_option = htmlspecialchars($_option,ENT_QUOTES,"UTF-8");
						$body .= '<option value="'.$h_option.'" ' .
								 (($_option == $value) ? 'selected="selected"' : "") .
								 '>' . $h_option . '</option>' . "\n";
					}
				}
				$body .= '</select>';

				break;
			case "multi":
				$h_value = (empty($h_value)) ? $config["defaultValue"] : $h_value ;
			
				$body = '<textarea class="textarea m-area liq-area"'
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
					   .' /></dd>' . 
					   '<dt>タイトル(title)</dt><dd><input type="text" class="s-area"'
					   .' id="'.$idAttribute.'"'
					   .' name="'.$h_formName.'[title]"'
					   .' value="'.@$h_value["title"].'" '
					   .' size="40" '
					   .' /></dd>' .
					    '<dt>代替テキスト(alt)</dt><dd><input type="text" class="s-area"'
					   .' id="'.$idAttribute.'"'
					   .' name="'.$h_formName.'[alt]"'
					   .' value="'.@$h_value["alt"].'" '
					   .' size="40" '
					   . ' /></dd>';
				
				
				$body .= ((strlen($h_value["src"])>0) ? '<dd><br /><img id="'.$idAttribute.'_img" src="'.htmlspecialchars($h_value["src"],ENT_QUOTES).'" /></dd>' : "") ;
				$body .= "</dl>"; 
				
				break;
			case "file":
				
				$body = '<span><input type="text" class="s-area"'
					   .' id="'.$idAttribute.'"'
					   .' name="'.$h_formName.'"'
					   .' value="'.$h_value.'" '
					   .' size="40" '
					   .' /></span> ';
				$body .= '<span><input type="button" class="s-btn" value="参照" onclick="entry_editor_show_attachments(function(img,link){$(\'#'.$idAttribute.'\').val(img);});" /></span>';
			
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
				$h_value = (empty($h_value)) ? $config["defaultValue"] : $h_value ;
				
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
						<textarea id="'.$idAttribute.'" name="'.$h_formName.'" class="m-area liq-area aobata_editor">'.$h_value.'</textarea>
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
				$body = '<input type="text" class="m-area liq-area"'
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
		
		$_html = '<div class="field-form '.$classAttribute.'">' . $body . '</div>';
			
		return $_html;
	}
}
?>