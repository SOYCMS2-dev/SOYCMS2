<?php
/**
 * 公開側ログイン
 */
class SiteUserLoginSession extends SOY2Session{
	
	/**
	 * 識別
	 */
	private $id;
	private $attributes = array();
	private $siteId;
	private $soycmsRoot;
	private $isDynamic = false;
	private $roles = array();
	
	function setAttribute($key,$value){
		$this->attributes[$key] = $value;
	}
	function getAttribuete($key){
		return (isset($this->attributes[$key])) ? $this->attributes[$key] : null;
	}
	
	function isDynamic() {
		return $this->isDynamic;
	}
	
	/* setter getter */
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}

	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	function getSoycmsRoot() {
		return $this->soycmsRoot;
	}
	function setSoycmsRoot($soycmsRoot) {
		$this->soycmsRoot = $soycmsRoot;
	}

	function getIsDynamic() {
		return $this->isDynamic;
	}
	function setIsDynamic($isDynamic) {
		$this->isDynamic = $isDynamic;
	}

	function getRoles() {
		return $this->roles;
	}
	function setRoles($roles) {
		$this->roles = $roles;
	}
}
?>