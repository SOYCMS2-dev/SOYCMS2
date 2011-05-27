<?php
SOY2HTMLFactory::importWebPage("FileManager.IndexPage");

/**
 * @class IndexPage
 * @date 2010-07-25T16:18:40+09:00
 * @author SOY2HTMLFactory
 */ 
class EditorPage extends IndexPage{
	
	function doPost(){
		if(isset($_POST["save"])){
			//保存
			$root = SOYCMS_SITE_DIRECTORY;
			$path = $_GET["url"];
			$filepath = realpath($root . $path);
			
			if(isset($_POST["file_content"]) && strlen($_POST["file_content"]) > 0){
				file_put_contents($filepath,$_POST["file_content"]);
			}
			
			$this->updated = true;
		}
		
		if(isset($_POST["resize"])){
			
		}
		
	}
	
	private $updated = false;
	
	function EditorPage($args){
		WebPage::WebPage();
		
		$this->buildHead();
		$this->buildPage();
		
		$this->addModel("updated",array("visible" => $this->updated));
	}	

	
	/**
	 * 繰り返しを作る
	 */
	function buildPage(){
		$this->addForm("form");
		
		$root = SOYCMS_SITE_DIRECTORY;
		$path = $_GET["url"];
		$filepath = realpath($root . $path);
		
		$this->addModel("file_not_exists",array("visible" => !$filepath));
		$this->addModel("file_exists",array("visible" => $filepath));
		
		$isImage = false;
		$ext = pathinfo($filepath);
		$isImage = preg_match('/(jpe?g|gif|png)$/i',$ext["extension"]);
		
		$isText = false;
		$isText = preg_match('/(css|html?|txt|xml|js)$/i',$ext["extension"]);
		
		$prefix = dirname($filepath);
		if(strlen($prefix) > 30)$prefix = substr($prefix,0,15) . "..." . substr($prefix,strlen($prefix) - 15);
		$filepath_text = $prefix . "/" . basename($filepath);
		$content = (!$isImage && $isText) ? file_get_contents($filepath) : "";
		
		$this->addLabel("file_path",array("text" => $filepath_text));	
		
		$this->addTextArea("file_content",array(
			"name" => "file_content",
			"value" => $content,
			"visible" => (!$isImage)
		));
		
		$this->addImage("image_preview",array(
			"src" => soycms_union_uri(SOYCMS_SITE_URL,$path),
			"visible" => $isImage
		));
		
		$this->addModel("is_not_image",array("visible" => !$isImage));
		$this->addModel("is_image",array("visible" => $isImage));
	}
	
	function getLayout(){
		return "blank.php";
	}
}


?>