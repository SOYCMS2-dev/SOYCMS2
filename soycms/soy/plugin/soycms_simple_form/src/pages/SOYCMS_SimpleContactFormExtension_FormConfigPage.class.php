<?php


class SOYCMS_SimpleContactFormExtension_FormConfigPage extends HTMLPage{

	function doPost(){

		if(isset($_POST["NewField"])){
			$config = $this->getConfig();

			$obj = SOY2::cast("SOYCMS_ContactFormField", $_POST["NewField"]);
			$items = $config["items"];
			if(!is_array($items)){
				$items = array();
			}
			
			if(strlen($obj->getId())<1 || !preg_match("/^[a-z][a-zA-Z0-9\-]+$/",$obj->getId())){
				$obj->setId($obj->getType());
			}
			
			//既に登録済みの場合
			if(isset($items[$obj->getId()])){
				$oldId = $obj->getId();
				$counter = 1;
				while(isset($items[$obj->getId()])){
					$id = $oldId . "_" . $counter;
					$obj->setId($id);
					$counter++;
				}
			}
			$items[$obj->getId()] = $obj;
			$config["items"] = $items;

			SOYCMS_ContactFormHelper::saveConfig($this->pageObj,$config);

			SOY2PageController::redirect(soycms_create_link("ext/soycms_simple_form?id=".$this->id."&field=".$obj->getId() . "&created"));
		}

		if(isset($_POST["Field"])){
			SOY2::cast($this->field,$_POST["Field"]);
			$config = $this->getConfig();
			$config["items"][$this->field->getId()] = $this->field;
			SOYCMS_ContactFormHelper::saveConfig($this->pageObj,$config);
			SOY2PageController::redirect(soycms_create_link("ext/soycms_simple_form?id=".$this->id."&field=".$this->fieldId . "&updated"));
		}


		SOY2PageController::redirect($this->getPageConfigURL());
	}


	private $id;
	private $fieldId;
	private $field;
	private $fields = array();
	private $mode = "detail";
	private $pageObj;

	function init(){
		try{
			$this->id = @$_GET["id"];
			$this->pageObj = SOY2DAO::find("SOYCMS_Page",$this->id);
			$this->fieldId = @$_GET["field"];

			$config = $this->getConfig();
			$this->fields = $config["items"];
			if(!is_array($this->fields))$this->fields = array();

			if($this->fieldId && isset($this->fields[$this->fieldId])){
				$this->field = $this->fields[$this->fieldId];
			}
		}catch(Exception $e){
			echo "failed";
			exit;
		}

		if(isset($_GET["mode"]))$this->mode = $_GET["mode"];
		if($this->mode == "remove" && soy2_check_token()){
			$config = $this->getConfig();
			unset($config["items"][$this->fieldId]);
			SOYCMS_ContactFormHelper::saveConfig($this->pageObj,$config);
			SOY2PageController::redirect($this->getPageConfigURL());
		}
	}


	function SOYCMS_SimpleContactFormExtension_FormConfigPage($args = array()) {
		$this->pageObj = @$args[0];

		HTMLPage::HTMLPage();


		$this->addModel("on_create",array("visible" => $this->mode == "create"));
		$this->addModel("on_detail",array("visible" => $this->mode == "detail"));
		$this->addModel("on_sample",array("visible" => $this->mode == "sample"));

		$this->buildPage();
		$this->buildForm();
		$this->buildSampleCode();

		$this->addForm("form");
	}

	function buildPage(){
		$this->addLink("page_link",array(
			"link" => soycms_create_link("page/detail/" . $this->id)
		));

		$this->addLink("page_config_link",array(
			"link" => $this->getPageConfigURL()
		));
		$this->addLabel("page_name",array(
			"text" => $this->pageObj->getName()
		));


		$this->addLabel("field_name",array(
			"text" => ($this->field) ? $this->field->getName() : ""
		));
	}

	function buildForm(){
		$field = ($this->field) ? $this->field : new SOYCMS_ContactFormField();
		$option = $field->getOptions();

		$this->addLabel("field_id_text",array(
			"text" => $field->getId()
		));

		$this->addInput("field_name_input",array(
			"name" => "Field[name]",
			"value" => $field->getName()
		));

		/* 種別ごとのオン、オフ */
		$this->addModel("type_input",array(
			"visible" => $field->getType() == "input"
		));
		$this->addModel("type_mailaddress",array(
			"visible" => $field->getType() == "mailaddress"
		));
		$this->addModel("type_checkbox",array(
			"visible" => $field->getType() == "checkbox"
		));
		$this->addModel("type_subitem",array(
			"visible" =>  $field->getType() == "radio"
						|| $field->getType() == "select"
						|| $field->getType() == "checkbox"
		));
		$this->addModel("require_option",array(
			"visible" => $field->getType() != "confirm"
		));
		$this->addModel("require_default",array(
			"visible" => $field->getType() != "mailaddress"
		));
		/* --種別ごとのオン、オフ */

		/* 種別毎の違い */
		$this->addSelect("field_validation_select",array(
			"name" => "Field[options][validation]",
			"value" => @$option["validation"],
			"options" => $field->getValidations()
		));
		$this->addInput("field_validation_regex",array(
			"name" => "Field[options][regex]",
			"value" => @$option["regex"],
		));
		$this->addCheckbox("field_mailaddress_confirm",array(
			"elementId" => "field_mailaddress_confirm",
			"name" => "Field[options][confirm]",
			"isBoolean" => true,
			"selected" => @$option["confirm"],
			"value" => 1,
		));
		$this->addInput("field_checkbox_separate",array(
			"name" => "Field[options][separate]",
			"value" => (empty($option["separate"])) ? "," : @$option["separate"],
		));
		$this->addTextArea("field_subitem",array(
			"name" => "Field[options][subitem]",
			"value" => @$option["subitem"]
		));
		/* --種別毎の違い */


		$this->addSelect("field_type",array(
			"name" => "Field[type]",
			"selected" => $field->getType(),
			"options" => SOYCMS_SimpleFormBuilder::getTypes()
		));

		$this->addInput("field_default_value",array(
			"name" => "Field[default]",
			"value" => $field->getDefault(),
		));

		$this->addCheckbox("field_require",array(
			"elementId" => "field_require",
			"name" => "Field[require]",
			"isBoolean" => true,
			"selected" => $field->getRequire(),
			"value" => 1,
		));


		//種別
		$this->addSelect("field_type_select",array(
			"name" => "NewField[type]",
			"options" => SOYCMS_SimpleFormBuilder::getTypes()
		));


	}

	function buildSampleCode(){
		$form = array();
		$confirm = array();
		$mail = array();

		$form[] = '<form cms:id="contact_form">';
		$form[] = '<p>必須項目をご入力ください。</p>';
		$form[] = '<table class="fix" summary="お問い合わせフォーム">';
		$form[] = '<caption>';
		$form[] = '<strong class="important">*入力必須</strong>';
		$form[] = '</caption>';
		$form[] = '<tbody>';

		$confirm[] = '<form cms:id="contact_form">';
		$confirm[] = '<p>入力内容をご確認下さい</p>';
		$confirm[] = '<table class="fix" summary="お問い合わせフォーム">';
		$confirm[] = '<caption>';
		$confirm[] = '<strong class="important">*入力必須</strong>';
		$confirm[] = '</caption>';
		$confirm[] = '<tbody>';



		foreach($this->fields as $key => $field){
			$option = $field->getOptions();
			$form[] = $this->buildSampleCodeByField($field);

			$confirm[] = '	<tr>';
			$confirm[] = '		<th>'.$field->getName().'</th>';
			$confirm[] = '		<td><!-- cms:id="contact_'.$key.'_text" -->'.$field->getName().'<!-- /cms:id="contact_'.$key.'_text" --></td>';
			$confirm[] = '	</tr>';

			$mail[] = $field->getName() . " -> " . "#".$key."#";

			if($field->getType() == "mailaddress" && @$option["confirm"] == 1){
				$_field = clone($field);
				$_field->setName($_field->getName() . "(確認)");
				$_field->setId($_field->getId() . "_confirm");
				$_field->setType("mailaddress_confirm");
				$form[] = $this->buildSampleCodeByField($_field);
			}
		}

		$form[] = '</tbody>';
		$form[] = '</table>';
		$form[] = '<p class="center">';
		$form[] = '<input type="submit" value="送信" id="sample-submit" name="sample-submit" class="l-btn" />';
		$form[] = '</p>';
		$form[] = '</form>';

		$confirm[] = '</tbody>';
		$confirm[] = '</table>';
		$confirm[] = '<p class="center">';
		$confirm[] = '<input cms:id="back_button" type="submit" value="戻る" class="m-btn" />';
		$confirm[] = '<input type="submit" value="送信" id="sample-submit" name="sample-submit" class="l-btn" />';
		$confirm[] = '</p>';
		$confirm[] = '</form>';


		$this->addTextArea("code_area",array(
			"value" => implode("\n",$form)
		));
		$this->addTextArea("confirm_code_area",array(
			"value" => implode("\n",$confirm)
		));
		$this->addTextArea("mail_code_area",array(
			"value" => implode("\n",$mail)
		));
	}

	function buildSampleCodeByField($field){
		$form = array();

		$id = $field->getId();
		$type = $field->getType();
		$name = $field->getName();
		$header = $name;
		$option = $field->getOptions();

		if($field->getRequire())$header = '<strong class="important">*</strong>' . $header;

		$form[] = '	<tr>';
		$form[] = '	<th scope="row" class="right">'.$header.'</th>';
		$form[] = '	<td>';
		if($field->getRequire())$form[] = '		<p class="important" cms:id="'.$id.'_require_error">'.$field->getName().'は必須項目です。</p>';

		$item = "";
		switch($type){
			case "mailaddress_confirm":
				$form[] = '		<p class="important" cms:id="'.$id.'_format_error">'.$name.'の書式が正しくありません。</p>';
				$form[] = '		<p class="important" cms:id="'.$id.'_error">メールアドレスが一致しません。</p>';
				$item = '<input type="text" cms:id="contact_'.$id.'" class="l-area" />';
				break;
			case "mailaddress":
				$form[] = '		<p class="important" cms:id="'.$id.'_format_error">'.$name.'の書式が正しくありません。</p>';
				$item = '<input type="text" cms:id="contact_'.$id.'" class="l-area" />';
				break;
			case "input":
				$form[] = '		<p class="important" cms:id="'.$id.'_format_error">'.$name.'の書式が正しくありません。</p>';
				$item = '<input type="text" cms:id="contact_'.$id.'" class="l-area" />';
				break;
			case "textarea":
				$item = '<textarea type="text" cms:id="contact_'.$id.'" class="l-area" rows="6"></textarea>';
				break;
			case "select":
				$item = '<select cms:id="contact_'.$id.'">'."\n<option value=\"\">選択して下さい</option>\n".'</select>';
				break;
			case "confirm":
				$item = '<input type="checkbox" cms:id="contact_'.$id.'" /><label for="contact_'.$id.'">同意する</label>';
				break;
			case "checkbox":
			case "radio":
				$items = explode("\n",@$option["subitem"]);
				$item = '<!-- cms:id="contact_'.$id.'" -->';
				foreach($items as $key => $_item){
					$_id = $id . "_" . $key;
					$_item = trim($_item);
					if(empty($_item))continue;
					$item .= "\n			";
					$item .= '<input type="'.$type.'" cms:id="contact_'.$_id.'" /><label for="contact_'.$_id.'">'.$_item.'</label>';
				}
				$item .= "\n" . '			<!-- /cms:id="contact_'.$id.'" -->';
				break;
		}

		$form[] = '		' . $item;


		$form[] = '	</td>';
		$form[] = '	</tr>';

		return implode("\n\t",$form);
	}

	function getPageConfigURL($suffix = null){
		$_suffix = "#tab1/config_advance";
		return soycms_create_link("page/detail/" . $this->id . $_suffix. $suffix);
	}

	function getConfig(){
		$config = SOYCMS_ContactFormHelper::getConfig($this->pageObj);

		return $config;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/".get_class($this).".html";
	}
}
?>