<?php

class TemplateList extends HTMLList{
	
	private $detailLink;
	private $removeLink;
	
	private $inputName = null;
	private $selected = null;
	private $types = array();
	
	function init(){
		if(count($this->list)<1){
			$list = SOYCMS_Template::getList();
			$this->setList($list);
		}
		
		$this->detailLink = soycms_create_link("/page/template/");
		$this->_soy2_parent->addModel("template_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("template_not_exists",array("visible"=>count($this->list)<1));
		
	}

	function populateItem($entity,$key){
		$this->addModel("template_row",array("class" => "template_" . $entity->getType()));
		$this->addInput("template_id",array("name" => "template_id", "value" => $entity->getId()));
		
		$this->addLabel("template_name",array("text"=>$entity->getName()));
		$this->addLabel("template_dir_name",array("text" => $this->getGroupName($entity->getId())));
		$this->addLabel("template_description",array("html"=>$entity->getDescription()));
		$this->addLabel("template_type",array("text"=>$entity->getTypeText()));
		$this->addLink("template_edit_link",array(
			"link"=> $this->detailLink . "detail?id=" .$entity->getId()
		));
		$this->addLink("template_template_link",array(
			"link"=> $this->detailLink . "detail?id=" .$entity->getId() . "#tpl_config"
		));
		$this->addLink("template_copy_link",array("link"=>
			$this->detailLink . "create?id=" .$entity->getId()));
			
			
		$type_img = str_replace(".","",$entity->getType()) . ".gif";
		$this->addImage("template_preview_image",array(
			"src" => SOYCMS_ROOT_URL . "common/img/tmpl_layout_".$type_img,
			"style" => "border-color:".$entity->getBorderColor().";"
		));
		$this->addModel("template_color_box",array(
			"style" => "color:" . $entity->getBorderColor().";"
		));
			
		$this->addLink("preview_link",array(
			"link" => SOYCMS_SITE_ROOT_URL . "?template_preview=" . $entity->getId() . "&" . soycms_get_ssid_token()
		));
			
			
		$pages = $this->getPagesByTemplate($entity->getId());
			
		$this->addLink("template_remove_link",array(
			"link"=>$this->detailLink . "remove?id=" .$entity->getId(),
			"visible" => (count($pages) < 1)
		));
		
		$this->addList("page_list",array(
			"list" => $pages,
			'populateItem:function($entity)' => '$this->addLink("page_public_link",array("link"=>soycms_union_uri("'.SOYCMS_SITE_URL.'",$entity->getUri())));' .
					'$this->addLink("page_link",array("link"=>"'.soycms_create_link("page/detail/") .'".$entity->getId(),"text"=>$entity->getName()));'
		));
		
		$this->addInput("template_order",array(
			"name" => "TemplateOrder[".$entity->getId()."]",
			"value" => ""
		));
		
		$this->addCheckBox("template_select_input",array(
			"name" => $this->getInputName(),
			"value" => $entity->getId(),
			"selected" => ($entity->getId() == $this->getSelected())
		));
		
		
		//表示する種別を指定していた場合
		if($this->types && !in_array($entity->getType(),$this->types)){
			return false;
		}
		
		
		
	}
	
	function getGroupName($id){
		$array = explode("/",$id);
		return substr($array[0],1);
	}
	
	private $_dao;
	
	function getPagesByTemplate($id){
		if(!$this->_dao)$this->_dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		
		if(strlen($id)>0){
			$res = $this->_dao->getByTemplate($id);
			return $res;
		}
		
		return array();
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}

	function getInputName() {
		return $this->inputName;
	}
	function setInputName($inputName) {
		$this->inputName = $inputName;
	}
	function setTypes($types){
		$this->types = $types;
	}
}
?>