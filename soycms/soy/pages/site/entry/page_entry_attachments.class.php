<?php
/**
 * 添付ファイル
 * - サムネイルの作成
 */
class page_entry_attachments extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["upload"])){
			$this->doUpload();
		}
	}
	
	private $id;
	private $entry;
	
	function init(){
		if(!$this->entry){
			$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		}
	}

	function page_entry_attachments($args) {
		$this->id = $args[0];
		if(count($args)>1)$this->entry = $args[1];
		
		//画像の削除
		if(isset($_GET["remove"]) && isset($_GET["image_path"])){
			if(soy2_check_token()){
				$this->doDelete($_GET["image_path"]);
			}
			$this->jump("/entry/detail/" . $this->id . "?deleted");
		}
		
		WebPage::WebPage();
		
	}
	
	function main(){
		$this->execute();
	}
	
	function execute(){
		$attachmentDir = $this->entry->getAttachmentPath();
		$url = $this->entry->getAttachmentUrl();
		
		$attachmentLink = soycms_create_link("/entry/attachments/" . $this->id);
		
		$files = soy2_scandir($attachmentDir);
		foreach($files as $key => $file){
			if(is_dir($attachmentDir . $file)){
				unset($files[$key]);
			}
		}
		
		$this->createAdd("attachment_list","SOYCMS_AttachmentList",array(
			"list" => $files,
			"url" => $url,
			"dir" => $attachmentDir,
			"entry" => $this->entry,
			"removeLink" => $attachmentLink . "?remove"
		));
		
		$this->addForm("form",array(
			"enctype" => "multipart/form-data",
			"action" => $attachmentLink
		));
		
		$this->addLabel("reload_url",array(
			"text" => $attachmentLink . "?reload=1&"
		));
		
		$this->addModel("is_action",array(
			"visible" => (!empty($_POST) || isset($_GET["reload"]))
		));
		
		$this->addCheckbox("create_thumbnail",array(
			"elementId" => "create_thumbnail",
			"value" => 1,
			"selected" => SOYCMS_DataSets::get("make_thumbnail",1)
		));
		
		$size = SOYCMS_DataSets::get("thumbnail_size.l",300);
		$this->addInput("thumbnail_size_l",array(
			"attr:id" => "thumbnail_size_l",
			"name" => "thumbnail_size[]",
			"value" => ($size) ? $size : 300
		));
		
		$size = SOYCMS_DataSets::get("thumbnail_size.m",140);
		$this->addInput("thumbnail_size_m",array(
			"attr:id" => "thumbnail_size_m",
			"name" => "thumbnail_size[]",
			"value" => ($size) ? $size : 140
		));
		
		$size = SOYCMS_DataSets::get("thumbnail_size.s",60);
		$this->addInput("thumbnail_size_s",array(
			"attr:id" => "thumbnail_size_s",
			"name" => "thumbnail_size[]",
			"value" => ($size) ? $size : 60
		));
		
		/* resize_auto */
		
		$this->addCheckbox("resize_auto",array(
			"elementId" => "resize_auto",
			"name" => "resize_auto",
			"value" => 1,
			"selected" => SOYCMS_DataSets::get("resize_image",0)
		));
		$this->addInput("resize_auto_width",array(
			"attr:id" => "resize_auto_width",
			"name" => "resize_auto_width",
			"value" => SOYCMS_DataSets::get("resize_image.width",1024)
		));
		$this->addInput("resize_auto_height",array(
			"attr:id" => "resize_auto_height",
			"name" => "resize_auto_height",
			"value" => SOYCMS_DataSets::get("resize_image.height","")
		));
		
		$this->addLabel("max_upload_size",array(
			"text" => ini_get("upload_max_filesize")
		));
	}
	
	function getLayout(){
		if(isset($_POST["upload"])){
			return "layer.php"; 
		}
		return "blank.php";
	}
	
	/**
	 * 画像のアップロード実行
	 */
	function doUpload(){
		$dir = $this->entry->getAttachmentPath();
		$isOverwrite = (isset($_POST["is_overwrite"])) ? (boolean)$_POST["is_overwrite"] : false;
		
		$files = @$_FILES["files"];
		
		foreach($files["name"] as $key => $file){
			$name = $files["name"][$key];
			$name = str_replace("%","",rawurlencode($name));
			$tmp_name = $files["tmp_name"][$key];
			$size = $files["size"][$key];
			
			if(!$isOverwrite){
				$array = pathinfo($name);
				$basename =  preg_replace('/\.'.$array["extension"].'$/','',$array["basename"]);
				$counter = 0;
				while(file_exists($dir . $name)){
					$name = $basename ."_" . $counter . "." . $array["extension"];
					$counter++;
				}
			}
			
			move_uploaded_file($tmp_name,$dir . $name);
			
			//image only
			if(@getimagesize($dir . $name)){
				$thumbnail = $dir . "thumb/" . $name;
				if(!file_exists(dirname($thumbnail))){
					soy2_mkdir(dirname($thumbnail));
				}
				soy2_resizeimage_maxsize($dir . $name, $thumbnail, 120);
				
				if(@$_POST["create_thumbnail"]){
					$thumbdir = $dir . "thumb/" . crc32($name) . "/";
					if(!file_exists($thumbdir)){
						soy2_mkdir($thumbdir);
					}
					foreach($_POST["thumbnail_size"] as $size){
						$this->createThumbnail($dir . $name, $thumbdir, $size);
					}
				}
				
				if(@$_POST["resize_auto"]){
					$imagepath = $dir . $name;
					$width = (strlen($_POST["resize_auto_width"])>0) ? $_POST["resize_auto_width"] : null;
					$height = (strlen($_POST["resize_auto_height"])>0) ? $_POST["resize_auto_height"] : null;
					soy2_resizeimage_max_width_height($imagepath, $imagepath, $width, $height);
				}
			}
		}
	}
	
	/**
	 * ファイルを削除します
	 */
	function doDelete($path){
		
		//念のため
		$path = preg_replace("/^\.\./","",$path);
		
		$this->entry = SOY2DAO::find("SOYCMS_Entry",$this->id);
		
		$dir = $this->entry->getAttachmentPath();
		$filepath = $dir . $path;
		
		if(file_exists($filepath)){
			$thumbnails = $this->entry->getThumbnails($path);
			
			foreach($thumbnails as $array){
				$thumbnail_path = $dir . $array["file"];
				@unlink($thumbnail_path);
				
			}
			
			@unlink($filepath);
		}
		
	}
	
	/**
	 * サムネイルを作成
	 * @param filepath
	 * @param toDir
	 * @param size
	 */
	function createThumbnail($filepath, $toDir, $size){
		if(function_exists("getimagesize")){
			list($width, $height, $type, $attr) = getimagesize($filepath);
		}
		
		else if(class_exists("Imagick")){
			$thumb = new Imagick($filepath);
			$width = $thumb->getImageWidth();
			$height = $thumb->getImageHeight();
			$thumb = null;	
		}
		
		else if(function_exists("NewMagickWand")){
			$thumb = NewMagickWand();
			MagickReadImage($thumb,$filepath);
			list($width,$height) = array(MagickGetImageWidth($thumb),MagickGetImageHeight($thumb));
			$thumb = null;
		}
		
		else{
			throw new Exception("soy2_resizeimage_maxsize is not avaiable.please install Imagick,NewMagickWand or GD");
		}
		
		if($width > $height){
			$height = $height * $size / $width;
			$width = $size;
		}else{
			$width = $width * $size / $height;
			$height = $size;
		}
		
		$height = floor($height);
		$width = floor($width);
		
		$tofilepath = $toDir . $width . "x" . $height . "_" . basename($filepath);
		
		soy2_resizeimage($filepath,$tofilepath,$width,$height);
	}
}

class SOYCMS_AttachmentList extends HTMLList{
	
	private $entry;
	private $url;
	private $dir;
	private $removeLink = "";
	
	function setRemoveLink($link){
		$this->removeLink = $link;
	}
	
	function populateItem($entity){
		
		$filename = $entity;
		if(strlen($filename) > 20){
			$filename = substr($entity,0,10) . "..." . substr($entity,strlen($entity) - 5);
		}
		
		$isImage = $this->isImage($entity);
		
		$this->addLink("filename",array(
			"text" => $filename,
			"link" => $this->url . $entity,
			"attr:title" => ($isImage) ? "image" : $entity
		));
		
		$this->addActionLink("file_remove_link",array(
			"link" => $this->removeLink . "&image_path=" . $entity
		));
		
		
		$this->addImage("image",array(
			"src" => $this->url . "thumb/" . $entity,
			"visible" => $isImage
		));
		
		$thumbnails = ($isImage) ? $this->entry->getThumbnails($entity) : array();
		$this->createAdd("thumbnail_list","SOYCMS_ThumbnailList",array(
			"url" => $this->url,
			"img" => $this->url . $entity,
			"list" => $thumbnails
		));
	}
	
	/**
	 * @return boolean
	 */
	function isImage($filename){
		$array = pathinfo($filename);
		$ext = strtolower(@$array["extension"]);
		
		$isImage = in_array($ext,array("jpg","jpeg","gif","png"));
		
		$this->addLabel("extension",array(
			"text" => $ext,
			"visible" => !$isImage
		));
		
		return $isImage;
	}
	
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}

	function getDir() {
		return $this->dir;
	}
	function setDir($dir) {
		$this->dir = $dir;
	}

	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
}

class SOYCMS_ThumbnailList extends HTMLList{
	
	private $url;
	private $img;
	
	function setList($list){
		uasort($list,create_function('$a,$b','return str_replace("x","",$a["size"]) < str_replace("x","",$b["size"]);'));
		parent::setList($list);
	}
	
	function populateItem($file){
		$this->addLink("thumbnail_link",array(
			"link" => $this->url . $file["file"],
			"attr:img" => $this->img,
			"text" => $file["size"]
		));
	}
	

	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}

	function getImg() {
		return $this->img;
	}
	function setImg($img) {
		$this->img = $img;
	}
}
?>