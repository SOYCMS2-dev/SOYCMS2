<?php

class page_page_detail_xml extends SOYCMS_WebPageBase{

	private $id;
	private $url;
	private $page;
	private $obj;
	private $config;
	private $type;
	private $xml;
	
	function doPost(){
		
		if($_POST["xml_content"]){
			file_put_contents($this->xml->getTemplateFilePath(),$_POST["xml_content"]);
		}
		
		$this->jump("page/detail/xml/" . $this->id,"?uri=" . $this->url . "&updated");
	}
	
	function init(){
		
		try{
			$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
			if(!$this->page->isDirectory()){
				throw new Exception("");
			}
			
			$this->obj = $this->page->getObject();
			
			//configの取得
			$configs = array(
				"rdf" => $this->obj->getFeedRDF(),
				"rss" => $this->obj->getFeedRSS(),
				"atom" => $this->obj->getFeedAtom()
			);
			
			foreach($configs as $key => $config){
				if($this->url == $config["uri"]){
					$this->type = $key;
					$this->config = $config;
					break;
				}
			}
			
			if(!$this->config){
				throw new Exception("");
			}
			
			$this->xml = SOY2DAO::find("SOYCMS_Page",array("uri" => soycms_union_uri($this->page->getUri(),$this->url)));
			
		}catch(Exception $e){
			$this->page = null;
		}
		
	}

	function page_page_detail_xml($args) {
		$this->id = $args[0];
		$this->url = $_GET["uri"];
		
		WebPage::WebPage();
		
		$this->addModel("file_not_exists",array(
			"visible" => (is_null($this->page))
		));
		
		$this->addModel("file_exists",array(
			"visible" => (!is_null($this->page))
		));
		
		if(is_null($this->page))$this->page = new SOYCMS_Page();
		
		/* build form */
		
		$this->addForm("form");
		
		$this->addLabel("xml_url",array(
			"text" => $this->url
		));
		
		$this->addTextArea("xml_content",array(
			"attr:id" => "xml_content",
			"name" => "xml_content",
			"value" => ($this->xml) ? file_get_contents($this->xml->getTemplateFilePath()) : ""
		));
		
		$this->addTextArea("default_xml_content",array(
			"attr:id" => "default_xml_content",
			"value" => ($this->xml) ? file_get_contents(SOY2::RootDir() . "site/domain/page/SOYCMS_DetailPage/template/" . $this->type . ".html") : ""
		));
		
	}
	
	function getLayout(){
		return "layer.php";
	}
}
?>