<?php

class ListPageFormPage extends HTMLPage{
	
	private $obj;
	private $page;
	
	function ListPageFormPage($obj){
		$this->obj = $obj;
		$this->page = $obj->getPage();
		HTMLPage::HTMLPage();
	}
	
	function main(){
		$this->createAdd("directory_tree","_class.list.PageTreeComponent",array(
			"type" => "detail",
			"checkboxName" => "object[directory]",
			"selected" => $this->obj->getDirectory() 
		));
		
		$this->addCheckbox("is_include_child",array(
			"name" => "object[isIncludeChild]",
			"value" => 1,
			"isBoolean" => 1,
			"label" => "子ディレクトリも対象にする",
			"selected" => $this->obj->getIsIncludeChild()
		));
		
		$this->createAdd("period_from","_class.component.HTMLDateInput",array(
			"name" => "object[periodFrom]",
			"value" => $this->obj->getPeriodFrom()
		));
		
		$this->createAdd("period_to","_class.component.HTMLDateInput",array(
			"name" => "object[periodTo]",
			"value" => $this->obj->getPeriodTo()
		));
		
		//ソート順
		$types = $this->obj->getSortTypes();
		for($i=0;$i<count($types);$i++){
			$key = $i+1;
			$this->addCheckbox("sort_type_".$key,array(
				"name" => "object[order]",
				"elementId" => "sort_type_" . $key,
				"value" => $types[$i],
				"selected" => ($types[$i] == $this->obj->getOrder())
			));
		}
		
		$this->addInput("limit",array(
			"name" => "object[limit]",
			"value" => $this->obj->getLimit()
		));
		
		//カスタムなど
		PluginManager::load("soycms.site.page.list");
		$delegetor = PluginManager::invoke("soycms.site.page.list",array("mode" => "list","moduleId" => $this->obj->getPlugin()));
		$plugins = $delegetor->getList();
		$this->addModel("plugin_exists",array(
			"visible" => count($plugins) > 0
		));
		$this->addSelect("plugin_select",array(
			"name" => "object[plugin]",
			"options" => $plugins,
			"selected" => $this->obj->getPlugin()
		));
		
		$plugin = ($this->obj->getPlugin()) ? $this->obj->getPluginObject($delegetor) : null;
		
		$items = array("directory","period","sort","limit");
		if($plugin)$items = $plugin->getRequireItems($items);
		
		$this->addModel("require_directory_item",array("visible" => in_array("directory",$items)));
		$this->addModel("require_period_item",array("visible" => in_array("period",$items)));
		$this->addModel("require_sort_item",array("visible" => in_array("sort",$items)));
		$this->addModel("require_limit_item",array("visible" => in_array("limit",$items)));
		
		$this->addLabel("plugin_config_form",array(
			"html" => ($plugin) ? $plugin->getConfigForm($this) : ""
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
	
	
}
?>