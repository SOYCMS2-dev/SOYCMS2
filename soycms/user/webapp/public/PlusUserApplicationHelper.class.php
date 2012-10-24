<?php

class PlusUserApplicationHelper {
	
	public static function getInstance($controller = null,$config = null){
		static $_instance;
		if(!$_instance){
			if(!$config)$config = PlusUserConfig::getConfig();
			$_instance = new PlusUserApplicationHelper($controller,$config);
		}
		return $_instance;
	}
	
	public static function getMemberPageUrl(){
		$inst = self::getInstance();
		$url = soycms_get_page_url($inst->config->getMemberPageUrl());
		return $url;
	}
	
	public static function getModulePageUrl($moduleId,$suffix = null,$query = array()){
		$inst = self::getInstance();
		$uri = soycms_get_page_url(
			$inst->config->getMemberPageUrl(),
			$inst->config->getModulePageUri($moduleId,$suffix,$query)
		);
		return $uri;
	}
	
	public static function getTopicPath(){
		return self::getInstance()->path;
	}
	
	public static function putModuleTopicPath($moduleId,$title,$suffix = null,$query = array()){
		$inst = self::getInstance();
		$uri = soycms_get_page_url(
			$inst->config->getMemberPageUrl(),
			$inst->config->getModulePageUri($moduleId,$suffix,$query)
		);
		self::putTopicPath($uri,$title);
	}
	
	public static function putTopicPath($uri,$title){
		$inst = self::getInstance();
		$inst->path[] = array(
			"uri" => $uri,
			"title" => $title
		);
	}
	
	private function PlusUserApplicationHelper($con,$config){
		$this->controller = $con;
		$this->config = $config;
	}
	
	private $controller;
	private $config;
	private $path = array();
	
	/**
	 * @return PlusUser_SiteController
	 */
	public static function getController(){
		return self::getInstance()->controller;
	}
	
	public static function getConfig(){
		return self::getInstance()->config;
	}
	
}
?>