<?php

class ModuleList extends HTMLList{
	
	private $link;
	private $memberPageUrl;
	private $siteUrl;
	
	function init(){
		$this->link = soycms_create_link("config/module");
		PluginManager::load("plus.user.module");
		$obj = PluginManager::invoke("plus.user.module",array("mode" => "list"));
		$list = $obj->getList();
		
		$array = array();
		
		foreach($list as $key => $value){
			if(!isset($this->list[$key])){
				$array[$key] = array(
					"active" => false,
					"url" => "",
					"defaultUrl" => "",
				);
			}else{
				$array[$key] = $this->list[$key];
			}
			
			$array[$key]["name"] = $value["name"];
			$array[$key]["description"] = $value["description"];
			$array[$key]["defaultUrl"] = $value["defaultUrl"];
			$array[$key]["login"] = $value["login"];
		}
		
		$this->list = $array;
		
	}
	
	function populateItem($entity,$key,$index){
		
		$this->addLabel("module_page_prefix",array(
			"text" => ($entity["login"]) ? $this->memberPageUrl : $this->siteUrl
		));
		
		$this->addCheckbox("module_check",array(
			"name" => "Module[$key][active]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $entity["active"],
			"tabindex" => ($index + count($this->list)) 
		));
		
		$this->addInput("module_url",array(
			"name" => "Module[$key][url]",
			"value" => (!empty($entity["url"])) ? $entity["url"] : $entity["defaultUrl"],
			"attr:tabindex" => $index
		));
		
		$this->addLabel("module_name",array(
			"text" => $entity["name"]
		));
		
		$this->addLink("module_detail_link",array(
			"link" => $this->link . "/" . $key
		));
		
		$this->addLabel("module_description",array(
			"text" => $entity["description"]
		));
		
	}



	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link = $link;
	}
	function getMemberPageUrl() {
		return $this->memberPageUrl;
	}
	function setMemberPageUrl($memberPageUrl) {
		$this->memberPageUrl = $memberPageUrl;
	}
	function getSiteUrl() {
		return $this->siteUrl;
	}
	function setSiteUrl($siteUrl) {
		$this->siteUrl = $siteUrl;
	}
}
?>