<?php

class SOYCMS_Skeleton {
	
	function export($filepath){
		$tmp = array();
		$tmp[] = '<?xml version="1.0" encoding="UTF-8" ?>';
		$tmp[] = '<skeleton>';
		$tmp[] = '<version>' . SOYCMS_VERSION . '</version>';
		$tmp[] = '<name>' . $this->name . '</name>';
		$tmp[] = '<description><![CDATA[ ' . $this->description . ' ]]></description>';
		$tmp[] = '<url><![CDATA[' . $this->url . ' ]]></url>';
		$tmp[] = '<aurthor><![CDATA[' . $this->author . ' ]]></aurthor>';
		$tmp[] = '<aurthor_url><![CDATA[' . $this->authorUrl . ' ]]></aurthor_url>';
		$tmp[] = '<information>';
		foreach($this->information as $key => $value){
			$tmp[] = '<'.$key.'>'.$value.'</'.$key.'>';
		}
		$tmp[] = '</information>';
		$tmp[] = '</skeleton>';
		file_put_contents($filepath,implode("\n",$tmp));
    }
    
    function import($filepath){
    	$xml = simplexml_load_file($filepath);
    	
    	$this->setId(basename(dirname($filepath)));
    	$this->setName((string)$xml->name);
    	$this->setDescription((string)$xml->description);
    	$this->setUrl((string)$xml->url);
    	$this->setAuthor((string)$xml->author);
    	$this->setAuthorUrl((string)$xml->author_url);
    	
    	if($xml->information){
	    	$infomation = (array)$xml->information;
	    	$info = array();
	    	foreach($infomation as $key => $value){
	    		$info[$key] = $value;
	    	}
	    	$this->setInformation($info);
    	}
    }
    
    /**
     * 全て取得
     */
    public static function get(){
    	
    	$dir = SOYCMS_ROOT_DIR . "content/skeleton/";
    	
    	//ディレクトリが無い場合
    	if(!file_exists($dir)){
    		return array();
    	}
    	
    	$files = soy2_scandir($dir);
    	$files = array_reverse($files);
    	
    	$res = array();
    	
    	foreach($files as $file){
    		if(file_exists($dir . $file . "/skeleton.xml")){
    			$obj = new SOYCMS_Skeleton();
    			$obj->setId($file);
    			$obj->import($dir . $file . "/skeleton.xml");
    			$res[$file] = $obj; 
    		}
    	}
    	
    	return $res;
    }
    
    public static function load($target){
    	$dir = SOYCMS_ROOT_DIR . "content/skeleton/";
    	if(file_exists($dir . $target . "/skeleton.xml")){
    		$obj = new SOYCMS_Skeleton();
    		$obj->import($dir . $target . "/skeleton.xml");
    		return $obj;
    	}
    	
    	return null;
    }

    private $id;
    
    private $dir;
    
    private $name;
    
    private $description;
    
    private $url;
    
    private $author;
    
    private $authorUrl;
    
    private $information = array();
    
    private $status = true;	//利用中
    
    /* getter setter */

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getDir() {
    	return $this->dir;
    }
    function setDir($dir) {
    	$this->dir = $dir;
    }
    function getName() {
    	return $this->name;
    }
    function setName($name) {
    	$this->name = $name;
    }
    function getDescription() {
    	return $this->description;
    }
    function setDescription($description) {
    	$this->description = $description;
    }
    function getUrl() {
    	return $this->url;
    }
    function setUrl($url) {
    	$this->url = $url;
    }
    function getAuthor() {
    	return $this->author;
    }
    function setAuthor($author) {
    	$this->author = $author;
    }
    function getAuthorUrl() {
    	return $this->authorUrl;
    }
    function setAuthorUrl($authorUrl) {
    	$this->authorUrl = $authorUrl;
    }

    function getInformation() {
    	return $this->information;
    }
    function setInformation($information) {
    	$this->information = $information;
    }

    function getStatus() {
    	return $this->status;
    }
    function setStatus($status) {
    	$this->status = $status;
    }
}

