<?php

class PluginInfo {
	
	function PluginInfo($id,$pluginDir) {
		$this->id = $id;
		$this->directory = $pluginDir;
	}
	
	function prepare(){
		$ini = $this->directory  . "plugin.ini";
		
		if(!file_exists($ini)){
			throw new Exception();
		}
		
		$array = parse_ini_file($ini,true);
		
		$this->name = @$array["name"];
		$this->description = @$array["description"];
		$this->version = @$array["version"];
		$this->types = explode(",",@$array["type"]);
		$this->data = $array; 
	}
	
	function load($extensionId){
		$dir = $this->directory;
		@include_once($dir . $extensionId . ".php");
	}
	
	function doActive($dir){
		file_put_contents($dir . $this->id . ".active",date("Y-m-d H:i:s"));
	}
	
	function removeActive($dir){
		unlink($dir . $this->id . ".active");
	}
	
	function toggleActive($dir){
		if(file_exists($dir . $this->id . ".active")){
			return $this->removeActive($dir);
		}else{
			return $this->doActive($dir);
		}
	}
	
	function checkActive($dir){
		$this->isActive = file_exists($dir . $this->id . ".active");
	}
	
	function checkType($type){
		return in_array($type,$this->types);
	}
	
	function isActive(){
		return $this->isActive;
	}
	
	private $id;
	private $directory;
	
	private $name;
	private $description;
	private $version;
	private $types = array();
	private $data;
	
	private $isActive = false;

	
	/* getter setter */


	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
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
	function getVersion() {
		return $this->version;
	}
	function setVersion($version) {
		$this->version = $version;
	}
	function getTypes() {
		return $this->types;
	}
	function setTypes($types) {
		$this->types = $types;
	}
	function getData() {
		return $this->data;
	}
	function setData($data) {
		$this->data = $data;
	}
	function getIsActive() {
		return $this->isActive;
	}
	function setIsActive($isActive) {
		$this->isActive = $isActive;
	}
}
?>