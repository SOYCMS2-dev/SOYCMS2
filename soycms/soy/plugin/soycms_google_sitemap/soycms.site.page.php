<?php
class SOYCMS_GoogleSoiteMapXMLConfigPage extends SOYCMS_SitePageExtension{
	
	function SOYCMS_CustomFieldConfigPage(){
		
		
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "XMLサイトマップの設定";
	}
	
	function doPost(){
		
		//generate
		if(isset($_POST["urlset"])){
			$this->generate($_POST["urlset"]);
			
			SOY2PageController::redirect(soycms_create_link("/ext/soycms_google_sitemap?created"));
		}else{
			$this->generate($this->getConfigure());
			
			SOY2PageController::redirect(soycms_create_link("/ext/soycms_google_sitemap?updated"));
		}
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		
		$config = $this->getConfigure();
		
		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function getConfigure(){
		$config = array();
		
		if(file_exists(SOYCMS_SITE_DIRECTORY . ".plugin/sitemap.conf")){
			$config = soy2_unserialize(file_get_contents(SOYCMS_SITE_DIRECTORY . ".plugin/sitemap.conf"));
			
		//XMLファイルを確認
		}else if(file_exists(SOYCMS_SITE_DIRECTORY . "sitemap.xml")){
			$xml = simplexml_load_file(SOYCMS_SITE_DIRECTORY . "sitemap.xml");
			$urlset = $xml->children("http://www.sitemaps.org/schemas/sitemap/0.9");
			
			foreach($urlset->url as $url){
				$config[(string)$url->loc] = $url;
			}
		}
		
		return $config;
	}
	
	function generate($config){
		$xml = array();
		$values = array();
		
		$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml[] = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
				'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ' .
				'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		foreach($config as $array){
			$values[$array["url"]] = $array;
			if($array["visible"] != 1){
				continue;
			}
			
			$xml[] = '  <url>';
			$xml[] = '    <loc>'.$array["url"].'</loc>';
			$xml[] = '    <lastmod>'.$array["udate"].'</lastmod>';
			$xml[] = '    <changefreq>'.$array["freq"].'</changefreq>';
			$xml[] = '    <priority>'.$array["priority"].'</priority>';
			$xml[] = '  </url>';
			
		}
		
		$xml[] = '</urlset>';
		
		file_put_contents(SOYCMS_SITE_DIRECTORY . "sitemap.xml", implode("\n",$xml));
		file_put_contents(SOYCMS_SITE_DIRECTORY . ".plugin/sitemap.conf", soy2_serialize($values));
		
	}
}
PluginManager::extension("soycms.site.page","soycms_google_sitemap","SOYCMS_GoogleSoiteMapXMLConfigPage");
