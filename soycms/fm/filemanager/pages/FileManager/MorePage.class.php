<?php

class MorePage extends WebPage{

    private $path;
    private $offset;
    private $limit = 50;
    
    function MorePage() {
    	$this->path = $_GET["path"];
    	$this->offset = $_GET["offset"];
    	
    	WebPage::WebPage();
    	
    	$this->buildPage();
    
    }
	
	function getPath(){
		if(!empty($this->path) && $this->path[strlen($this->path) -1] != "/"){
			$this->path .= "/";
		}
		return $this->path;
	}
	
	function buildPage(){
    	
    	$root = SOYCMS_SITE_DIRECTORY;
		$path = $this->getPath();
		$path = $root . $path;
    	
    	$files = soy2_scandir($path);
    	
		$this->createAdd("file_list","FileManager.FileList",array(
			"dir" =>  $path,
			"list" => $files,
			"limit" => $this->limit,
			"offset" => $this->offset
		));
    	
    	$this->addLabel("file_total_count",array(
			"text" => count($files),
		));
		
		$offset = min($this->offset + $this->limit,count($files));
		
		$this->addLabel("file_current_count",array(
			"text" => $offset
		));
		
		$this->addModel("more_link_wrap",array(
			"visible" => count($files) > ($this->offset + $this->limit)
		));
		
		
		$this->addLink("more_link",array(
			"link" => SOY2PageController::createLink("FileManager.More?path=".$this->getPath()."&offset=" . $offset)
		));
    }
    
    function getLayout(){
    	return "blank";
    }
}
?>