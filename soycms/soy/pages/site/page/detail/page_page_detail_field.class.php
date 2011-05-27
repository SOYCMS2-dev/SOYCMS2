<?php
SOY2HTMLFactory::importWebPage("page.page_page_detail");
/**
 * @title ディレクトリの設定 > カスタムフィールド
 */
class page_page_detail_field extends page_page_detail{
	
	function doPost(){
		if(isset($_POST["delete_column"])){
			$keys = $_POST["delete_column"];
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("entry-" . $this->id);
			foreach($keys as $key => $val){
				unset($fields[$key]);
			}
			SOYCMS_ObjectCustomFieldConfig::saveConfig("entry-" . $this->id,$fields);
			$this->jump("/page/detail/field/".$this->id."?removed");
		}
		
		if(isset($_POST["FieldOrder"])){
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("entry-" . $this->id);
			
			$res = array();
			foreach($_POST["FieldOrder"] as $key => $value){
				$res[$key] = $fields[$key];
			}
			
			SOYCMS_ObjectCustomFieldConfig::saveConfig("entry-" . $this->id,$res);
		}
		
		if(isset($_POST["content-position"])){
			$this->page->setConfigParam("content-position",$_POST["content-position"]);
			$this->page->save();
		}
		
		if(isset($_POST["NewField"])){
			$field = SOY2::cast("SOYCMS_ObjectCustomFieldConfig",$_POST["NewField"]);
			
			//commonに登録
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			$fields[$field->getFieldId()] = $field;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("common",$fields);
			
			//ページに登録
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("entry-" . $this->id);
			$fields[$field->getFieldId()] = $field;
			SOYCMS_ObjectCustomFieldConfig::saveConfig("entry-" . $this->id,$fields);
			
			//今作成した項目を追加する
			$_POST["new_field"] = $field->getFieldId();
		}
		
		if(isset($_POST["new_field"]) && isset($_POST["append-field"])){
			$fieldId = $_POST["new_field"];
			$entry = SOYCMS_ObjectCustomFieldConfig::loadConfig("entry");
			$fields = SOYCMS_ObjectCustomFieldConfig::loadConfig("entry-" . $this->id);
			$common = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
			
			if(!isset($entry[$fieldId])){
				$fields[$fieldId] = $common[$fieldId];
			}
			
			SOYCMS_ObjectCustomFieldConfig::saveConfig("entry-" . $this->id,$fields);
			
		}
		
		$this->jump("/page/detail/field/".$this->id."?updated");
		
	}
	
	private $config;
	

	function page_page_detail_field($args) {
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->buildTab();
		$this->buildPage();
		$this->buildForm();
	}
	
	function buildPage(){
		parent::buildPage();
		
		$this->addLink("create_field_link",array(
			"link" => soycms_create_link("/page/field/create") . "?page=" . $this->id . "&layer"
		));
		
		$this->addLink("append_field_link",array(
			"link" => soycms_create_link("/page/field/select") . "?type=entry-" . $this->id
		));
		
		$this->addLink("field_code_link",array(
			"link" => soycms_create_link("/page/field/code") . "?type=entry-" . $this->id
		));
		
		$this->addLink("page_entry_edit_link",array(
			"link" => soycms_create_link("/page/detail/") . $this->id . "?entry",
			"visible" => !$this->page->isDirectory()
		));
		
		
		
		
	}
	
	function buildForm(){
		
		$pageFields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $this->id);
		$this->createAdd("field_list","_class.list.CustomFieldConfigList",array(
			"list" => $pageFields
		));
		
		$entryFields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry");
		$this->createAdd("common_field_list","_class.list.CustomFieldConfigList",array(
			"list" => $entryFields
		));
		
		$this->addForm("add_form");
		$this->addForm("new_field_form");
		$this->addForm("list_form");
		$this->addForm("form");
		
		$this->addSelect("field_select",array(
			"name" => "new_field",
			"options" => SOYCMS_ObjectCustomFieldConfig::getRegisteredTypes(array_keys($entryFields),array_keys($pageFields))
		));
		
		$this->addSelect("field_type_select",array(
			"name" => "NewField[type]",
			"options" => SOYCMS_ObjectCustomFieldConfig::getTypes()
		));
		
		
		$positionArray = array(
			"" => "通常の位置に表示",
			-1 => "表示しない",
		);
		foreach(array_merge($pageFields,$entryFields) as $key => $field){
			$positionArray[$key] = "[" . $field->getName() . "] の後ろに表示";
		}
		$this->addSelect("position",array(
			"name" => "content-position",
			"options" => $positionArray,
			"selected" => $this->page->getConfigParam("content-position")
		));
		
		
	}
}
?>