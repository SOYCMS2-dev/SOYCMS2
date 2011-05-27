<?php

class page_page_detail_sitemap extends SOYCMS_WebPageBase{

	function page_page_detail_sitemap() {
		WebPage::WebPage();
		
		//サイトマップ
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		
		$this->createAdd("sitemap","_class.list.PageTreeComponent",array(
			"list" => $pages
		));	
	}
	
	function getLayout(){
		return "blank.php";
	}
	
}
?>