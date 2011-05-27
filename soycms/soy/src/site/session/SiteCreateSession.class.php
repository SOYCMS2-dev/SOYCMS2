<?php

class SiteCreateSession extends SOY2Session{

	private $site;
	private $config = array();
	
	

	function getSite() {
		return $this->site;
	}
	function setSite($site) {
		$this->site = $site;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>