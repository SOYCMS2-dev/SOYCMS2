<?php
SOY2::import("zip.SOYCMS_ZipHelper");

/**
 * @title WEBデザインのコピー
 */
class page_manager_skeleton_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(!SOYCMS_ZipHelper::check(true)){
			return $this->notify("この環境では使うことが出来ません");
		}
		
		//ファイル名
		$filename = "skeleton";
		
		//export
		$logic = SOY2Logic::createInstance("site.logic.skeleton.SOYCMS_SkeletonManager");
		$filename = $logic->exportSkeleton($filename,$_POST,@$_FILES["thumbnail"]);
		
		//zip作成が完了したら(ダウンロードへのリンクを出力する)
		$link = soycms_create_link("manager/skeleton?download=") . $filename;
		
		$this->notify(
			$filename . "を作成しました。<br /><a href=\"$link\" target='target_fr'>自動的にダウンロードが開始されない場合</a>",
			$link
		);
		
		exit;
		
	}

	function page_manager_skeleton_index(){
		
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
		
		$this->addUploadForm("form");
		$this->buildForm();
	}
	
	function buildPages(){
		
		$this->addModel("zip_error_message",array(
			"visible" => !SOYCMS_ZipHelper::check(true)
		));
			

	}
	
	function buildForm(){
		$this->addInput("skeleton_name",array(
			"name" => "Skeleton[name]",
			"value" =>  SOYCMS_DataSets::get("site_name","")
		));
		
		$this->addTextArea("skeleton_description",array(
			"name" => "Skeleton[description]",
			"value" =>  "Site Template of " . SOYCMS_DataSets::get("site_name","")
		));
		
		$this->addInput("skeleton_author",array(
			"name" => "Skeleton[author]",
			"value" =>  SOYCMS_DataSets::get("site_autor","")
		));
		
		$this->addInput("skeleton_author_url",array(
			"name" => "Skeleton[authorUrl]",
			"value" => ""
		));
		
		/* contents からコピペ */
		/* build tree */
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		
		$this->createAdd("page_list","export_PageTreeComponent",array(
			"list" => $pages
		));
		
		/* design からコピペ */
		
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