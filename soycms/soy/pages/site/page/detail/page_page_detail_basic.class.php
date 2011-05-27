<?php
/**
 * @title ディレクトリ一覧
 */
class page_page_detail_basic extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;

	function page_page_detail_basic($args){
		
		$this->id = @$args[0];
		$this->page = @$args[1];
		
		WebPage::WebPage();
			
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		
		$this->addLabel("site_url",array("text" => SOYCMS_SITE_URL));
		$this->addLabel("page_type_text",array("text" => $this->page->getTypeText()));
		
	}
	
	function buildForm(){
		
		$this->addModel("is_not_directory",array(
			"visible" => !$this->page->isDirectory()
		));
		
		$this->addInput("page_name",array(
			"name" => "Page[name]",
			"value" => $this->page->getName()
		));
		
		$this->addInput("page_uri",array(
			"name" => "Page[uri]",
			"value" => $this->page->getUri()
		));
		$type = $this->page->getType();
		$this->addModel("uri_editable",array(
			"visible" => $type[0] != "."
		));
		
		$config = $this->page->getConfigObject();
		
		$this->addInput("page_config_title",array(
			"name" => "Page[config][title]",
			"value" => @$config["title"]
		));
		$this->addTextArea("page_config_description",array(
			"name" => "Page[config][description]",
			"value" => @$config["description"]
		));
		$this->addInput("page_config_keyword",array(
			"name" => "Page[config][keyword]",
			"value" => @$config["keyword"]
		));
		$this->addInput("page_config_encoding",array(
			"name" => "Page[config][encoding]",
			"value" => @$config["encoding"],
		));
		$this->addSelect("page_config_encoding_select",array(
			"selected" => @$config["encoding"],
			"options" => array("UTF-8","EUC-JP","Shift-JIS")
		));
		$this->addInput("page_config_type",array(
			"name" => "Page[config][content-type]",
			"value" => @$config["content-type"]
		));
		
		//公開設定
		$this->addCheckbox("page_config_public_1",array(
			"name" => "Page[config][public]",
			"value" => 1,
			"selected" => (@$config["public"] == 1),
			"elementId" => "public-cfg-1",
		));
		$this->addCheckbox("page_config_public_0",array(
			"name" => "Page[config][public]",
			"value" => 0,
			"selected" => (@$config["public"] == 0),
			"elementId" => "public-cfg-2",
		));
		$this->addCheckbox("page_config_public_2",array(
			"name" => "Page[config][public]",
			"value" => 2,
			"selected" => (@$config["public"] == 2),
			"elementId" => "public-cfg-3",
		));
		$this->addInput("page_config_public_2_option_id",array(
			"name" => "Page[config][public_option_id]",
			"value" => @$config["public_option_id"]
		));
		$this->addInput("page_config_public_2_option_pass",array(
			"name" => "Page[config][public_option_pass]",
			"value" => @$config["public_option_pass"]
		));
		
		$this->addModel("page_config_public_2_option_wrap",array(
			"style" => (@$config["public"] != 2) ? "display:none;" : ""
		));
		$this->addCheckbox("page_config_public_3",array(
			"name" => "Page[config][public]",
			"value" => 3,
			"selected" => (@$config["public"] == 3),
			"elementId" => "public-cfg-4",
		));
		
		
		/* 拡張設定 */
		$this->addLabel("page_advanced_config",array(
			"html" => $this->page->getPageObject()->getConfigPage()
		));
		
		//icon
		$this->addImage("page_icon",array(
			"src" => (strlen(@$config["icon"])>0) ? soycms_union_uri(SOYCMS_ROOT_URL,"content/" . SOYCMS_LOGIN_SITE_ID . "/" . @$config["icon"]) : "",
			"visible" => (strlen(@$config["icon"])>0)
		));
		
		$this->addImage("page_favicon",array(
			"src" => (strlen(@$config["favicon"])>0) ? soycms_get_page_url(@$config["favicon"]) : "",
			"visible" => (strlen(@$config["favicon"])>0)
		));
		
		$this->addInput("favicon_url",array(
			"value" => soycms_get_page_url(@$config["favicon"]),
			"visible" => (strlen(@$config["favicon"])>0)
		));
		
		//tab
		$this->addModel("type_error",array(
			"visible" => $this->page->getType() == ".error"
		));
	}
	
	function getLayout(){
		return "blank";
	}
}