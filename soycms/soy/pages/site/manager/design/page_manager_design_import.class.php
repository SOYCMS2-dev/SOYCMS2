<?php
SOY2::import("zip.SOYCMS_ZipHelper");

/**
 * @title WEBデザインのバックアップから復元
 */
class page_manager_design_import extends SOYCMS_WebPageBase{
	
	function init(){
		if(!SOYCMS_ZipHelper::check(true)){
			exit;
		}
	}
	
	function doPost(){
		
		$skeletonManager = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		
		//解凍してリダイレクト
		if(isset($_FILES["design_file"])){
			$targetDir = SOYCMS_LOGIN_SITE_ID . "_design";
			$filepath = $_FILES["design_file"]["tmp_name"];
			
			$skeletonManager->uncompress($filepath,SOYCMS_ROOT_DIR . "tmp/" . $targetDir);
			$this->jump("manager/design/import?target=" . $targetDir);
			exit;
		}
		
		//復元実行
		if(isset($_POST["uncompress"])){
			$skeletonManager->importDesign($this->target,$_POST);
		}
		
		$this->jump("manager?updated=design");
		
	}
	
	private $target;

	function page_manager_design_import(){
		$this->target = @$_GET["target"];
		
		WebPage::WebPage();
		
		if(!isset($_GET["target"]) || file_exists($this->target)){
			$this->jump("manager?failed");
		}
		
		$this->buildPages();
		
		$this->addForm("form");
		$this->buildForm();
	}
	
	function buildPages(){
		
		$this->addModel("zip_error_message",array(
			"visible" => !SOYCMS_ZipHelper::check()
		));
			

	}
	
	function buildForm(){
		
		//ターゲット
		$targetDir = SOYCMS_ROOT_DIR . "tmp/" . $this->target . "/";
		
		$this->createAdd("template_list","page_manager_design_import_ItemList",array(
			"formName" => "Template[]",
			"list" => (file_exists($targetDir . ".template/")) ? SOYCMS_Template::getList($targetDir . ".template/") : array()
		));
		
		$this->createAdd("library_list","page_manager_design_import_ItemList",array(
			"formName" => "Library[]",
			"list" => (file_exists($targetDir . ".library/")) ? SOYCMS_Library::getList($targetDir . ".library/") : array()
		));
		
		$this->createAdd("snippet_list","page_manager_design_import_ItemList",array(
			"formName" => "Snippet[]",
			"list" => (file_exists($targetDir . ".snippet/")) ? SOYCMS_Snippet::getListByDirectory($targetDir . ".snippet/") : array()
		));
		
		$this->createAdd("navigation_list","page_manager_design_import_ItemList",array(
			"formName" => "Navigation[]",
			"list" => (file_exists($targetDir . ".navigation/")) ? SOYCMS_Navigation::getList($targetDir . ".navigation/") : array(),
		));
		
		//
		$themes = (file_exists($targetDir . "themes/")) ? soy2_scandir($targetDir . "themes/") : array();
		$this->createAdd("theme_list","page_manager_design_import_ItemList",array(
			"formName" => "themes[]",
			"list" => $themes
		));
	}
	
	function overwrite($src,$dst){
		
		if(file_exists($dst)){
			soy2_delete_dir($dst);
		}
		
		soy2_copy($src,$dst,array($this,"convertURL"));
		
	}
	
	function convertURL($file){
		if(strpos($file,".html") === false)return;
		
		if(!@$this->host){
			$this->url = soycms_get_site_url(true);
			$array = parse_url($this->url);
			$this->host = strtolower($array["scheme"]) . "://" . $array["host"] . "/";
			$this->path = $array["path"];
		}
	
		$content = file_get_contents($file);
		$content = str_replace("@@SITE_PATH@@", $this->path ,$content);
		$content = str_replace("@@SITE_HOST@@",$this->host,$content);
		
		file_put_contents($file,$content);
	}
}

class page_manager_design_import_ItemList extends HTMLList{
	
	private $formName;
	private $selected = true;
	
	function populateItem($entity){
		
		$this->addCheckbox("checkbox",array(
			"name" => $this->formName,
			"value" => (is_string($entity)) ? $entity : $entity->getId(),
			"label" => (is_string($entity)) ? $entity : $entity->getName(),
			"selected" => $this->selected
		));
	}

	function getFormName() {
		return $this->formName;
	}
	function setFormName($formName) {
		$this->formName = $formName;
	}
	
	function setSelected($selected){
		$this->selected = $selected;
	}
}