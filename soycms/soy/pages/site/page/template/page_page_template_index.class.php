<?php
/**
 * @title テンプレートの管理
 */
class page_page_template_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["TemplateOrder"])){
			$orders = $_POST["TemplateOrder"];
			$orders = array_keys($orders);
			SOYCMS_DataSets::put("template.order",$orders);
		}
		
		$this->jump("/page/template");
	}

	function page_page_template_index(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		list($templates,$group) = $this->getTemplates();
		
		$this->createAdd("template_list","_class.list.TemplateList",array(
			"list" => $templates
		));
		$this->createAdd("group_list","TemplateGroupList",array(
			"list" => $group,
		));
		$this->addModel("group_list_exists",array(
			"visible" => count($group) > 0
		));
		
	}
	
	function getTemplates(){
		$res = array();$group = array();
		$list = SOYCMS_Template::getList();
		foreach($list as $key => $template){
			if($template->getGroup()){
				if(!isset($group[$template->getGroup()])){
					$group[$template->getGroup()] = SOYCMS_Template::getTemplateGroup($template->getGroup());
					$group[$template->getGroup()]["templates"] = array(
						$key => $template
					);
				}else{
					$group[$template->getGroup()]["templates"][$key] = $template;
				}
			}else{
				$res[$key] = $template;
			}
		}
		
		return array($res,$group);
	}
}

class TemplateGroupList extends HTMLList{
	
	private $link;
	
	function init(){
		$this->link = soycms_create_link("page/template/group");
	}
	
	function populateItem($entity,$key){
		$this->addLink("list_link",array(
			"link" => $this->link . "?groupId=" . $key
		));	
		$this->addLabel("group_name",array(
			"text" => $entity["name"]
		));	
		
		$this->createAdd("group_template_list","_class.list.TemplateList",array(
			"list" => $entity["templates"]
		));
	}
	
}