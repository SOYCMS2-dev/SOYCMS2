<?php
SOY2::import("zip.SOYCMS_ZipHelper");

/**
 * @title コンテンツのバックアップから復元
 */
class page_manager_contents_import extends SOYCMS_WebPageBase{
	
	function init(){
		if(!SOYCMS_ZipHelper::check()){
			exit;
		}
	}
	
	function doPost(){
		
		$skeletonManager = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		
		//解凍してリダイレクト
		if(isset($_FILES["contents_file"])){
			$targetDir = SOYCMS_LOGIN_SITE_ID . "_contents";
			$filepath = $_FILES["contents_file"]["tmp_name"];
			
			//解凍
			$skeletonManager->uncompress($filepath,SOYCMS_ROOT_DIR . "tmp/" . $targetDir);
			
			$this->jump("manager/contents/import?target=" . $targetDir);
			exit;
		}
		
		//復元実行
		if(isset($_POST["uncompress"])){
			$skeletonManager->importContents($this->target,$_POST);
			
			//update mapping
			SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic")->updatePageMapping();
		}
		
		$this->jump("manager?updated=contents");
		
	}
	
	private $target;

	function page_manager_contents_import(){
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
		$dsn = "sqlite:" . $targetDir . "contents/site.db";
		$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$pageDAO->setDsn($dsn);
		$entryDAO->setDsn($dsn);
		
		$this->addLabel("page_count",array(
			"text" => $pageDAO->countByType("detail")
		));
		
		$this->addLabel("entry_count",array(
			"text" => $entryDAO->count()
		));
		
	}
	
}

class page_manager_contents_import_ItemList extends HTMLList{
	
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