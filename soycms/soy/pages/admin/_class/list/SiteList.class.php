<?php

class SiteList extends HTMLList{
	
	private $theme = "gray";
	private $detailLink;
	private $loginLink;
	private $rootSiteId;
	
	function init(){
		$this->rootSiteId = SOYCMS_CommonConfig::get("DomainRootSite",null);
		
		$userSession = SOY2Session::get("base.session.UserLoginSession");
		if($userSession){
			$this->theme = $userSession->getTheme();
		}
	}
	
	function populateItem($entity){
		$this->addModel("site_row",array(
			"attr:id" => "site-" . $entity->getId()
		));
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("site_id","HTMLLabel",array(
			"text" => $entity->getSiteId()
		));
		
		$this->createAdd("site_name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		//標準のfaviconを使う
		$faviIcon = null;
		if(file_exists($entity->getPath() . "themes/icons/favicon.ico")){
			$faviIcon = "themes/icons/favicon.ico";
		}
		
		$theme = $this->theme;
		
		$this->addImage("site_favicon_img",array(
			"src" => ($faviIcon) ? $entity->getUrl() . $faviIcon
			 : SOYCMS_COMMON_URL . "cp_theme/$theme/favicon.ico",
		));
		
		$this->createAdd("site_url","HTMLLink",array(
			"text" =>
				(($this->rootSiteId == $entity->getSiteId()) ? "(＊)" : "") .
				$entity->getUrl(),
			"link" => $entity->getUrl(),
		));
		
		if(!$this->detailLink)$this->detailLink = soycms_create_link("/site/detail");
		
		$this->createAdd("detail_link","HTMLLink",array(
			"link" => $this->detailLink . "/" . $entity->getId()
		));
		
		if(!$this->loginLink)$this->loginLink = soycms_create_link("/site/login");
		
		$this->createAdd("login_link","HTMLLink",array(
			"link" => $this->loginLink . "/" . $entity->getId() . "?login"
		));
	}
	


	public function getTheme(){
		return $this->theme;
	}

	public function setTheme($theme){
		$this->theme = $theme;
		return $this;
	}

	public function getDetailLink(){
		return $this->detailLink;
	}

	public function setDetailLink($detailLink){
		$this->detailLink = $detailLink;
		return $this;
	}

	public function getRootSiteId(){
		return $this->rootSiteId;
	}

	public function setRootSiteId($rootSiteId){
		$this->rootSiteId = $rootSiteId;
		return $this;
	}

	public function getLoginLink(){
		return $this->loginLink;
	}

	public function setLoginLink($loginLink){
		$this->loginLink = $loginLink;
		return $this;
	}
}
?>