<?php

class FileList extends HTMLList{

    private $dir;
    private $offset = 0;
    private $limit = 100;
    
    function init(){
    	
    	$list = $this->list;
    	$dir = $this->getDir();
    	$path = str_replace(SOYCMS_SITE_DIRECTORY,"",$dir);
    	$url = SOY2PageController::createLink("FileManager");
    	
    	$dirs = array();
    	$files = array();
    	
    	$counter = 0;
    	foreach($list as $file){
    		if($file == "index.php")continue;
    		
    		$filepath = $dir . $file;
    		if(is_dir($filepath)){
    			$dirs[] = $file;
    		}else{
    			$files[] = $file;
    		}
    	}
    	$list = array_merge($dirs,$files);
    	$items = array();
    	
    	
    	foreach($list as $file){
    		$counter++;
    		if($counter <= $this->offset){
    			continue;
    		}
    		if($counter > ($this->limit + $this->offset)){
    			break;
    		}
    		
    		$filepath = $dir . $file;
    		
    		if(is_dir($filepath)){
    			$suffix = "/-/" . $path . $file;
    			$count = count(soy2_scandir($filepath));
    			
    			$items[] = array(
    				"name" => $file . " [".$count."]",
    				"type" => "Folder",
    				"url" => soycms_union_uri($path , $file),
    				"size" => "",
    				"class"=> "folder",
    				"onclick" => "",
    				"link" => (is_readable($filepath)) ? $url . $suffix : "",
    				"public" => "",
    				"link_onclick" => "common_click_dir('$path"."$file');",
    				"date" => "",
    			);
    		}else{
    			$items[] = array(
    				"name" => $file,
    				"type" => $this->getType($file),
    				"url" => soycms_union_uri($path , $file),
    				"size" => $this->getFileSize($filepath),
    				"class" => "",
    				"onclick" => '$(this).hide().next().show().select();',
    				"link" => 'javascript:void(0);',
    				"link_onclick" => "common_open_editor('".soycms_union_uri($path , $file)."');return false;",
    				"date" => $this->getFileDateString(filemtime($filepath))
    			);
    		}
    		
    		
    	}
    	
    	$list = $items;
    	
    	if(strlen($path)>0 && $this->offset < 1){
    		$dir = soy2_realpath(dirname($this->getDir()));
    		$path = str_replace(SOYCMS_SITE_DIRECTORY,"",$dir);
    		
    		$suffix = (strlen($path) > 0) ? "/-/" . $path : "";
    		$link = $url . $suffix;
    		
    		array_unshift($list,array(
    			"name" => "..",
				"type" => "Folder",
				"url" => $path,
				"size" => "",
				"class"=> "folder",
				"link" => $link,
				"link_onclick" => "common_click_dir('$path');",
    		));
    	}
    	
    	
    	$this->setList($list);
    	
    }
    
    function populateItem($file){
    	
    	$this->addModel("file_list_row",array(
    		"class" => @$file["class"] . " file_row",
    	));
    	
    	$this->addLink("file_link",array(
    		"link" => $file["link"],
    		"onclick" => $file["link_onclick"],
    		"attr:title" => soycms_union_uri(SOYCMS_SITE_URL,$file["url"]),
		));
    	
    	$this->addLink("url_link",array(
    		"link" => "javascript:void(0);",
    		"onclick" => @$file["onclick"],
    		"visible" => (strlen(@$file["onclick"])>0)
    	));
    	
    	$this->addModel("file_link_input",array(
    		"value" => soycms_union_uri(SOYCMS_SITE_URL,$file["url"]) 
    	));
    	
    	$this->addLabel("file_name",array(
    		"text" => rawurldecode($file["name"])
    	));
    	
    	$this->addLabel("file_type",array(
    		"text" => $file["type"]
    	));
    	
    	$this->addLabel("file_size",array(
    		"text" => $file["size"]
    	));
    	
    	$this->addLabel("file_date",array(
    		"text" => @$file["date"]
    	));
    	
    }
    
    function getType($file){
    	$array = pathinfo($file);
    	$ext = strtolower(@$array["extension"]);
    	
    	return $ext;
    }
    
    function getFileSize($file){
    	$int = filesize($file);
    	$digit = 2;
    	
    	if($int >= pow(1024, 4)){
			$int_t = round($int / pow(1024, 4), $digit);
			$int_t .= "T";
		}elseif($int >= pow(1024, 3)){
			$int_t = round($int / pow(1024, 3), $digit);
			$int_t .= "G";
		}elseif($int >= pow(1024, 2)){
			$int_t = round($int / pow(1024, 2), $digit);
			$int_t .= "M";
		}elseif($int >= 1024){
			$int_t = round($int / 1024, $digit);
			$int_t .= "K";
		}elseif($int < 1024){
			$int_t = round($int, $digit);
		}
		return $int_t;
    }
    
    function getFileDateString($time){
    	if(date("Ymd") == date("Ymd",$time)){
    		return date("H:i:s",$time);
    	}
    	
    	return date("Y-m-d",$time);
    }

    function getDir() {
    	return $this->dir;
    }
    function setDir($dir) {
    	$this->dir = $dir;
    }

    function getLimit() {
    	return $this->limit;
    }
    function setLimit($limit) {
    	$this->limit = $limit;
    }

    function getOffset() {
    	return $this->offset;
    }
    function setOffset($offset) {
    	$this->offset = $offset;
    }
}
?>