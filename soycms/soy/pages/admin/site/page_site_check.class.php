<?php
SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("site.domain.SOYCMS_Role");

/**
 * サイトにログインするかどうかの確認
 */
class page_site_check extends SOYCMS_WebPageBase{
	
	private $id;
	private $site;
	
	function init(){
		try{
			$this->site = $site = SOY2DAO::find("SOYCMS_Site",$this->id);
			$session = SOY2Session::get("site.session.SiteLoginSession");
			$res = $session->login($site,SOYCMS_LOGIN_USER_ID,true);
			if(!$res){
				throw new Exception("");
			}
		}catch(Exception $e){
			$this->jump("/site/select");
		}
	}

	function page_site_check($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
		
		$this->addLabel("site_name",array(
			"text" => $this->site->getName()
		));
		$this->addLink("site_login_url",array(
			"link" => soycms_create_link("/site/login/" . $this->id)
		));
		
		$rootSite = SOYCMS_CommonConfig::get("DomainRootSite",null);
		
		$isRootSite = ($rootSite == $this->site->getSiteId());
		$siteLink = ($isRootSite) ? 
					  SOY2PageController::createRelativeLink("/",true) 
					: $this->site->getUrl();
		
		$this->addLink("site_link",array(
			"link" => $siteLink
		));
		$this->addLabel("site_url",array(
			"text" => ($isRootSite) ? "(*)" . $siteLink : $siteLink
		));
		
		$config = SOYCMS_DataSets::get("config_custom",array());
		
		$this->addImage("site_image",array(
			"src" => (!file_exists(SOYCMS_ROOT_DIR . "content/header_icon_" . $this->site->getSiteId() . ".png"))
						 ? SOYCMS_COMMON_URL . "cp_theme/gray/img/logo.png"
						 : SOYCMS_ROOT_URL . "content/header_icon_" . $this->site->getSiteId() . ".png"
		));
	}
	
	function getLayout(){
		return "login.php";
	}
}
?>