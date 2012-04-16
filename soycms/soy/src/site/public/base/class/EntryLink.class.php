<?php
class EntryLink extends HTMLLink{
	
	private $dir = null;
	
	function execute(){
		
		$_dir = $this->getAttribute("cms:dir");
		if($_dir){
			$this->dir = $_dir;
		}
		$this->setLink(soycms_union_uri($this->dir,$this->link));
		
		parent::execute();
	}
	
	function setLink($link){
		$link = preg_replace('/\/index\.html$/',"/",$link);
		parent::setLink($link);
	}
	
	function setDir($dir){
		$dir = str_replace("/index.html","/",$dir);
		$this->dir = $dir;
	}

}
?>