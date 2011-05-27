<?php 
/**
 * @class IndexPage
 * @date 2010-07-25T16:18:40+09:00
 * @author SOY2HTMLFactory
 */ 
class IndexPage extends WebPage{
	
	function doPost(){
		if(isset($_POST["upload"])){
			FileManager::uploadPath($this->getPath());
			FileManager::doUpload();
		}
		
		if(isset($_POST["mkdir"])){
			FileManager::uploadPath($this->getPath());
			FileManager::createDirectory($_POST["new_directory_name"]);
		}
		
		if(isset($_POST["remove"])){
			FileManager::removeFile($this->getPath(),$_POST["remove_file"]);
		}
		
		if(isset($_POST["rename"]) 
		&& isset($_POST["old_name"])
		&& isset($_POST["new_name"])
		&& strlen($_POST["old_name"]) > 0
		&& strlen($_POST["new_name"]) > 0
		){
			$old = $_POST["old_name"];
			$name = $_POST["new_name"];
			FileManager::renameFile($this->getPath(),$old,$name);
		}
		
		if(strlen($this->path) > 0){
			SOY2PageController::jump("FileManager/-/" . $this->path);
		}else{
			SOY2PageController::jump("FileManager");
		}
		exit;
		
	}
	
	private $path;
	private $limit = 10;
	
	function IndexPage($args){
		$this->path = implode("/",@$args);
		WebPage::WebPage();
		
		$this->buildHead();
		$this->buildPage();
	}
	
	function getPath(){
		if(!empty($this->path) && $this->path[strlen($this->path) -1] != "/"){
			$this->path .= "/";
		}
		return $this->path;
	}
	
	/**
	 * 繰り返しを作る
	 */
	function buildPage(){
		$root = SOYCMS_SITE_DIRECTORY;
		$path = $this->getPath();
		$path = $root . $path;
		$files = soy2_scandir($path);
		
		$this->addLabel("path_text",array("text" => $path));
		
		$this->createAdd("file_list","FileManager.FileList",array(
			"dir" =>  $path,
			"list" => $files,
			"limit" => $this->limit
		));
		
		$this->addForm("form",array(
			"enctype" => "multipart/form-data"
		));
		
		$this->addForm("mkdir_form");
		$this->addForm("rename_form");
		$this->addForm("remove_form");
		
		$this->addInput("upload_path",array(
			"name" => "upload_path",
			"value" => $this->path
		));
		
		$this->addLabel("upload_path_text",array(
			"text" => $path
		));
		
		$this->addLabel("file_total_count",array(
			"text" => count($files),
		));
		
		$this->addLabel("file_current_count",array(
			"text" => $this->limit
		));
		
		$this->addModel("more_link_wrap",array(
			"visible" => count($files) > $this->limit
		));
		
		$this->addLink("more_link",array(
			"link" => SOY2PageController::createLink("FileManager.More?path=".$this->getPath()."&offset=" . $this->limit)
		));

		$this->addLabel("max_upload_size",array(
			"text" => ini_get("upload_max_filesize")
		));
	}
	
	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery.js",
			$root . "jquery-ui.js",
			$root . "main.js",
			SOYCMS_COMMON_URL . "js/jquery.cookie.js",
			$root . "design.js"
		);
	}
	
	function getStyles(){
		$root = SOY2PageController::createRelativeLink("./css/");
		return array(
			$root . "styles.css",
			$root . "design.css",
			$root . "fm.css",
		);
	}
	
	
	function buildHead(){
		$head = $this->getHeadElement();
		
		$styles = $this->getStyles();
		foreach($styles as $style){
			$head->appendHTML('<link rel="stylesheet" href="'.$style.'" />');
		}
		
		$head->appendHTML('<script type="text/javascript">var SOYCMS_ROOT_URL = "'.SOYCMS_ROOT_URL.'";</script>');
		
		$scripts = $this->getScripts();
		foreach($scripts as $script){
			$head->appendHTML('<script type="text/javascript" src="'.$script.'"></script>');
		}
		
		
	}
	
	function getLayout(){
		return "blank.php";
	}
}


?>