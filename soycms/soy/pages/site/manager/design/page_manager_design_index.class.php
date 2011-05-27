<?php
SOY2::import("zip.SOYCMS_ZipHelper");

/**
 * @title WEBデザインのコピー
 */
class page_manager_design_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(!SOYCMS_ZipHelper::check(true)){
			return $this->notify("この環境では使うことが出来ません");
		}
		
		if(!isset($_POST["filename"])){
			exit;
		}
		
		$filename = $_POST["filename"];
		$filename = str_replace(array(".","/"),"_",$filename);
		if(strlen($filename) < 1){
			$filename = SOYCMS_LOGIN_SITE_ID . "_design";
		}
		
		//export
		$logic = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		$filename = $logic->exportDesign($filename,$_POST);
		
		//zip作成が完了したら(ダウンロードへのリンクを出力する)
		$link = soycms_create_link("manager/design?download=") . $filename;
		
		
		$this->notify(
			$filename . "を作成しました。<br /><a href=\"$link\" target='target_fr'>自動的にダウンロードが開始されない場合</a>",
			$link
		);
		exit;
		
	}

	function page_manager_design_index(){
		
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
			"visible" => !SOYCMS_ZipHelper::check()
		));
			

	}
	
	function buildForm(){
		$this->addInput("filename",array(
			"name" => "filename",
			"value" => SOYCMS_LOGIN_SITE_ID . "_design" 
		));
		
		$this->createAdd("template_list","page_manager_design_index_ItemList",array(
			"formName" => "Template[]",
			"list" => SOYCMS_Template::getList()
		));
		
		$this->createAdd("library_list","page_manager_design_index_ItemList",array(
			"formName" => "Library[]",
			"list" => SOYCMS_Library::getList()
		));
		
		$this->createAdd("snippet_list","page_manager_design_index_ItemList",array(
			"formName" => "Snippet[]",
			"list" => SOYCMS_Snippet::getList()
		));
		
		$this->createAdd("navigation_list","page_manager_design_index_ItemList",array(
			"formName" => "Navigation[]",
			"list" => SOYCMS_Navigation::getList(),
		));
		
		//
		$themes = soy2_scandir(SOYCMS_SITE_DIRECTORY . "themes/");
		$this->createAdd("theme_list","page_manager_design_index_ItemList",array(
			"formName" => "themes[]",
			"list" => $themes
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
	
	function convertURL($file){
		if(strpos($file,".html") === false)return;
		
		if(!@$this->host){
			$this->url = soycms_get_site_url(true);
			$array = parse_url($this->url);
			$this->host = strtolower($array["scheme"]) . "://" . $array["host"] . "/";
			$this->path = $array["path"];
		}
	
		
		$content = file_get_contents($file);
		$content = str_replace($this->url,"@@SITE_PATH@@",$content);
		$content = str_replace($this->host,"@@SITE_HOST@@",$content);
		$content = str_replace($this->path, "@@SITE_PATH@@",$content);
		file_put_contents($file,$content);
		
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