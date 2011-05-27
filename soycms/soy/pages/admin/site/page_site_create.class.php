<?php
/**
 * @title サイトの追加
 */
class page_site_create extends SOYCMS_WebPageBase{

	function page_site_create($args){
		WebPage::WebPage();
		
		$this->createAdd("site_init","HTMLModel",array(
			"attr:id" => "init_site_fr",
			"src" => SOY2FancyURIController::createRelativeLink("../site/?init_site")
		));
		
	}
	
	function getLayout(){
		$siteDAO = SOY2DAOFactory::create("SOYCMS_SiteDAO");
		$sites = $siteDAO->get();
		
		if(count($sites)<1){
			return "init.php";
		}
		
		return parent::getLayout();
	}
}