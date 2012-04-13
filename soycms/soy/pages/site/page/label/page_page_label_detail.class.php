<?php
 
/**
 * @title ラベル詳細
 */ 
class page_page_label_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		SOY2::cast($this->label,(object)$_POST["Label"]);
		$label = $this->label;
		
		$label->setId($this->id);
		
		try{
			if($label->check()){
				$label->save();
			}
			
			$id = $label->getId();
			
			//customfield
			if(isset($_POST["LabelCustomField"])){
				SOYCMS_ObjectCustomField::setValues("label",$id,$_POST["LabelCustomField"]);
			}
			
			
			
			$this->jump("/page/label/detail/$id?updated");
		}catch(Exception $e){
			
		}
	}
	
	protected $id;
	protected $label;
	
	function init(){
		try{
			$this->label = SOY2DAO::find("SOYCMS_Label",($this->id));
		}catch(Exception $e){
			$this->jump("/page/label");
		}
	}
	
	function page_page_label_detail($args){
		$this->id = @$args[0];
		
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPages();
	}
	
	function buildForm(){
		
		$label = $this->label;
		$config = $label->getConfigObject();
		
		$this->addForm("form");
		
		$this->addInput("label_name",array(
			"name" => "Label[name]",
			"value" => $label->getName(),
		));
		
		$this->addInput("label_alias",array(
			"name" => "Label[alias]",
			"value" => $label->getAlias(),
		));
		
		
		$this->addInput("title_format",array(
			"name" => "Label[config][title]",
			"value" => (@$config["title"]) ? @$config["title"] : "#LabelName# - #DirName# - #SiteName#"
		));
		
		$this->addInput("label_bg_color",array(
			"name" => "Label[config][bgcolor]",
			"value" => @$config["bgcolor"]
		));
		
		$this->addInput("label_color",array(
			"name" => "Label[config][color]",
			"value" => @$config["color"]
		));
		
		$this->addTextArea("label_description",array(
			"name" => "Label[config][description]",
			"value" => @$config["description"]
		));
		
		$this->addModel("is_directory_label",array(
			"visible" => !$label->isCommon()
		));
	}
	
	function buildPages(){
		$this->addLink("remove_link",array(
			"link" => soycms_create_link("page/label/remove/" . $this->id)
		));
		$this->addLink("detail_link",array(
			"link" => soycms_create_link("page/label/detail/" . $this->id)
		));
		
		$mapping = SOYCMS_DataSets::get("site.page_mapping");
		$dir = $this->label->getDirectory();
		$url = "";
		$page = array();
		
		//対象のディレクトリが無い場合
		if(!isset($mapping[$dir])){
			$this->label->setType(0);
			//ディレクトリを指定いた場合は保存する
			if($dir){
				$this->label->save();
			}
		}else{
			$page = $mapping[$dir];
		}
		
		$this->addLabel("directory_url_text",array(
			"text" => soycms_get_page_url(@$page["uri"])
		));
		
		$this->addLink("label_index_link",array(
			"link" => soycms_union_uri(soycms_get_page_url(@$page["uri"]), rawurlencode($this->label->getAlias()))
		));
		
		$this->addLabel("directory_name",array(
			"text" => @$page["name"]
		));
		
		$this->addLink("directory_config_link",array(
			"link" => soycms_create_link("/page/detail/" . $dir)
		));
		
		
		//カスタムフィールド
		
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
		$this->addModel("customfile_exists",array(
			"visible" => count($configs)
		));
		
		$this->createAdd("field_list","_class.list.CustomFieldList",array(
			"list" => $configs,
			"objectId" => $this->id,
			"formName" => "LabelCustomField",
			"values" => SOYCMS_ObjectCustomField::getValues("label",$this->id)
		));
	} 
}


?>