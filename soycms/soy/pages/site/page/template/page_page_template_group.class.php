<?php
/**
 * @title テンプレートの管理
 */
class page_page_template_group extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["TemplateOrder"])){
			$orders = $_POST["TemplateOrder"];
			$orders = array_keys($orders);
			SOYCMS_DataSets::put("template.order",$orders);
		}
		
		$this->jump("/page/template");
	}
	
	function init(){
		try{
			$this->groupId = @$_GET["groupId"];
			$this->group = SOYCMS_Template::getTemplateGroup($this->groupId);
		}catch(Exception $e){
			$this->jump("/page/template?failed");
		}
	}

	private $groupId;
	private $group;

	function page_page_template_group(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$templates = $this->getTemplates();
		
		$this->createAdd("template_list","_class.list.TemplateList",array(
			"list" => $templates
		));
		
		$this->addLabel("group_name",array(
			"text" => $this->group["name"]
		));
		
	}
	
	function getTemplates(){
		$res = array();$group = array();
		$list = SOYCMS_Template::getList();
		foreach($list as $key => $template){
			if($template->getGroup() && $template->getGroup() == $this->groupId){
				$res[$key] = $template;
			}
		}
		
		return $res;
	}
}
