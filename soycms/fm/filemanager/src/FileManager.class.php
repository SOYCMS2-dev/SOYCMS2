<?php

class FileManager {
	
	public static function uploadPath($path = null){
		static $_path;
		if($path)$_path = $path;
		return $_path; 
	}
	
	/**
	 * @return boolean
	 */
    public static function doUpload(){
    	$dir = self::getUploadDirectory();
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
	    		}
    		}
    		
    		move_uploaded_file($tmp_name,$dir . $name);
    	}
    }
    
    public static function createDirectory($name){
    	//remove [.]&[..]
    	$name = preg_replace("/^\.+/","",$name);
    	$name = preg_replace("/\//","_",$name);
    	
    	$path = self::getUploadDirectory();
    	
    	if(!file_exists($path . $name)){
	    	umask(0755);
	    	mkdir($path . $name);
	    	chmod($path . $name, 0755);
    	}
    }
	
	public static function getUploadDirectory(){
		$dir = SOYCMS_SITE_DIRECTORY;
		$path = self::uploadPath();
		
		return $dir . $path;
	}
	
	public static function removeFile($path,$file){
		$path = self::getUploadDirectory() . $path;
		
		if(is_dir($path . $file)){
			soy2_delete_dir($path . $file);
		}else{
			unlink($path . $file);
		}
	}
	
	public static function renameFile($path,$old,$name){
		$path = self::getUploadDirectory() . $path;
		
		$filepath = $path . $old;
		$newpath = $path . $name;
		
		rename($filepath, $newpath);	
	}

	
}
?>