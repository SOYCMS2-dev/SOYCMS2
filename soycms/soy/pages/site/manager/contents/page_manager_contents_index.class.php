<?php
SOY2::import("zip.SOYCMS_ZipHelper");

/**
 * @title コンテンツのコピー
 */
class page_manager_contents_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(!SOYCMS_ZipHelper::check(true)){
			return $this->notify("この環境では使うことが出来ません");
		}
		
		if(!isset($_POST["filename"])){
			exit;
		}
		
		set_time_limit(0);
		
		$filename = $_POST["filename"];
		$filename = str_replace(array(".","/"),"_",$filename);
		if(strlen($filename) < 1){
			$filename = SOYCMS_LOGIN_SITE_ID . "_contents";
		}
		
		$logic = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		$filename = $logic->exportContents($filename,$_POST);
		
		//zip作成が完了したら(ダウンロードへのリンクを出力する)
		$link = soycms_create_link("manager/contents?download=") . $filename;
		
		$this->notify(
			$filename . "を作成しました。<br /><a href=\"$link\" target='target_fr'>自動的にダウンロードが開始されない場合</a>",
			$link
		);
		exit;
		
	}

	function page_manager_contents_index(){
		
		if(isset($_GET["download"]) && strlen($_GET["download"])>0){
			if($_GET["download"][0] == ".")exit;
			if(file_exists(SOYCMS_ROOT_DIR . "tmp/" . $_GET["download"])){
				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-Type: application/zip");
				header("Content-Disposition: attachment; filename=" . $_GET["download"]);
				echo file_get_contents(SOYCMS_ROOT_DIR . "tmp/" . $_GET["download"]);
				exit;
			}
			exit;
		}
		
		WebPage::WebPage();
		
		$this->buildPages();
		
		$this->addForm("form");
		$this->buildForm();
	}
	
	function buildPages(){
		
		$this->addModel("zip_error_message",array(
			"visible" => !SOYCMS_ZipHelper::check(true)
		));
			

	}
	
	function buildForm(){
		$this->addInput("filename",array(
			"name" => "filename",
			"value" => SOYCMS_LOGIN_SITE_ID . "_contents" 
		));
		
		/* build tree */
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		
		$this->createAdd("page_list","export_PageTreeComponent",array(
			"list" => $pages
		));
		
		
	}
	
	function notify($message,$downpath = null){
		echo "<html>";
		echo "<head>";
		echo "<script type='text/javascript'>";
		echo 'window.parent.notify_message("'.addslashes($message).'");';
		if($downpath){
			echo 'window.parent.notifySuccess("'.$downpath.'");';
		}
		echo "</script>";
		echo "</head>";
		echo "<body>";
		echo "</body>";
		echo "</html>";
		
		exit;
	}
	
	
}

class page_manager_design_index_ItemList extends HTMLList{
	
	private $formName;
	private $selected = true;
	
	function populateItem($entity){
		
		$this->addCheckbox("checkbox",array(
			"name" => $this->formName,
			"value" => (is_string($entity)) ? $entity : $entity->getId(),
			"label" => (is_string($entity)) ? $entity : $entity->getName(),
			"selected" => $this->selected
		));
		
		$this->addLabel("id_text",array(
			"text" => (is_string($entity)) ? $entity : $entity->getId()
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

SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");

class export_PageTreeComponent extends PageTreeComponent{
	
	function populateItem($entity,$key,$depth,$isLast){
		if(!$entity instanceof SOYCMS_Page){
			$entity = new SOYCMS_Page();
		}
		
		$this->addCheckbox("export_config",array(
			"elementId" => "export_config_" . $entity->getId(),
			"name" => "pages[]",
			"value" => $entity->getId(),
			"selected" => true
		));
		
		$this->addModel("export_config_label",array(
			"attr:for" => "export_config_" . $entity->getId()
		));
		
		$this->addCheckbox("export_entry",array(
			"elementId" => "export_entry_" . $entity->getId(),
			"name" => "entries[]",
			"value" => $entity->getId(),
			"selected" => true
		));
		
		$this->addInput("export_entry_hidden",array(
			"name" => "entries[]",
			"value" => $entity->getId(),
		));
		
		$this->addModel("export_entry_label",array(
			"attr:for" => "export_entry_" . $entity->getId()
		));
		
		$this->addCheckbox("export_attachments",array(
			"elementId" => "export_attachments_" . $entity->getId(),
			"name" => "attachments[".$entity->getId()."]",
			"value" => $entity->getId(),
			"selected" => true
		));
		
		$this->addModel("export_attachments_label",array(
			"attr:for" => "export_attachments_" . $entity->getId()
		));
		
		return parent::populateItem($entity,$key,$depth,$isLast);
	}
	
}