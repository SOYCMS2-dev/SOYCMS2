<?php

class page_page_field_code extends SOYCMS_WebPageBase{
	
	private $type = null;
	private $config = "common";

	function page_page_field_code() {
		if(isset($_GET["type"]))$this->type = @$_GET["type"];
		if(isset($_GET["config"]))$this->config = @$_GET["config"];
		
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPage();
		
	}
	
	function buildForm(){
		$this->addForm("form");
	}
	
	function buildPage(){
		
		$list = SOYCMS_ObjectCustomFieldConfig::loadConfig($this->config);
		$configs = ($this->type) ? SOYCMS_ObjectCustomFieldConfig::loadObjectConfig($this->type) : $list;
		
		$this->addTextArea("code_area",array(
			"value" => $this->getSampleCode($configs,$list)
		));

	}
	
	function getSampleCode($configs,$list = null){
		$res = array();
		foreach($configs as $key => $value){
			if($list && !isset($list[$key]))continue;
			
			$config = ($list) ? $list[$key] : $value;
			$fieldId = $key;
			
			
			//
			switch($config->getType()){
				case "group":
					$res[] = "<h4>" . $config->getName() . "</h4>";
					break;
				default:
					$res[] = "<h5>" . $config->getName() . "</h5>";
					break;
			}
			
			//複数追加
			if($config->isMulti()){
				$res[] = "<!-- cms:id=\"{$fieldId}_list\" /-->";
			}
			
			$default = false;
			
			switch($config->getType()){
				case "group":
					$_fields = $config->getFields();
					$res[] = "<div>";
					$res[] = "" . $this->getSampleCode($_fields);
					$res[] = "</div>";
					break;
				case "check":
					$res[] = '<!-- cms:id="'.$fieldId.'" cms:separate="," -->チェック<!-- /cms:id="'.$fieldId.'" -->';
					$res[] = '';
					$res[] = '<!-- cms:id="'.$fieldId.'_list"  -->';
					$res[] = '	<span cms:id="check_value">値</span>';
					$res[] = '<!-- /cms:id="'.$fieldId.'_list"  -->';
					break;
				case "image":
					$res[] = "<p>";
					$res[] = "<img cms:id=\"{$fieldId}_image\" />";
					$res[] = "</p>";
					$default = true;
					break;
				case "url":
					$res[] = "<p>";
					$res[] = "<a cms:id=\"{$fieldId}_link\" />";
					$res[] = "<!-- cms:id=\"$fieldId\" /-->";
					$res[] = "</a>";
					$res[] = "</p>";
					$default = false;
					break;
				case "date":
				case "datelabel":
					$res[] = "<p>";
					$res[] = "<!-- cms:id=\"$fieldId\" cms:format=\"Y年n月j日\" /-->";
					$res[] = "ex)Timeタグを使った場合:<time cms:id=\"$fieldId\" cms:format=\"Y年n月j日\" cms:pubdate=\"Y-m-d\">".date("Y年n月j日")."</time>";
					$res[] = "</p>";
					break;
				default:
					$default = true;
			}
			
			if($default){
				$res[] = "<p>";
				$res[] = "<!-- cms:id=\"$fieldId\" /-->";
				$res[] = "</p>";
			}
			
			//複数追加
			if($config->isMulti()){
				$res[] = "<!-- /cms:id=\"{$fieldId}_list\" /-->";
			}
			
			$res[] = "";
		}
		
		
		return implode("\n",$res);
	}
	
	function getLayout(){
		return "layer.php";
	}
}
