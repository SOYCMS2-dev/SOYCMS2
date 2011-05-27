<?php

class DetailPageFormPage extends HTMLPage{
	
	private $obj;
	private $page;
	private $url;
	private $xmlLink;
	
	function DetailPageFormPage($obj){
		$this->obj = $obj;
		$this->page = $obj->getPage();
		HTMLPage::HTMLPage();
	}
	
	function main(){
		$this->addLabel("page_url",array(
			"text" => $this->getUrl() 
		));
		
		$this->addInput("default_title",array(
			"name" => "object[defaultTitle]",
			"value" => $this->obj->getDefaultTitle() 
		));
		
		$this->addInput("index_uri",array(
			"name" => "object[indexUri]",
			"value" => $this->obj->getIndexUri()
		));
		
		//url type
		$type = $this->obj->getUrlType();
		for($i=0;$i<=6;$i++){
			$this->addCheckbox("url_type_" . $i,array("name"=>"object[urlType]","value"=>$i,"selected"=>($type == $i)));
		}
		$this->addInput("url_type_option",array("name"=>"object[urlTypeOption]","value"=>$this->obj->getUrlTypeOption()));
		
		$this->xmlLink = soycms_create_link("page/detail/xml/") . $this->page->getId();
		
		
		//feed
		$this->addCheckbox("is_output_feed",array(
			"name" => "object[isOutputFeed]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => $this->obj->getIsOutputFeed()
		));
		
		$this->addInput("feed_entry_count",array(
			"name" => "object[feedEntryCount]",
			"value" => $this->obj->getFeedEntryCount()
		));
		
		$this->addFeedOption("rdf","feedRDF",$this->obj->getFeedRDF());
		$this->addFeedOption("rss","feedRSS",$this->obj->getFeedRSS());
		$this->addFeedOption("atom","feedAtom",$this->obj->getFeedAtom());
		
		//entry_position
		$this->addSelect("entry_position",array(
			"name" => "object[entryPosition]",
			"options" => array("-1"=>"先頭に追加","1"=>"末尾に追加"),
			"selected" => $this->obj->getEntryPosition()
		));
	}
	
	function getUrl(){
		if(!$this->url){
			$url = soycms_get_page_url($this->page->getUri());
			if($url[strlen($url)-1] != "/")$url .= "/";
			$this->url = $url;
		}
		return $this->url;
	}
	
	function addFeedOption($key,$name,$array){
		
		$this->addCheckbox($key . "_output",array(
			"name" => "object[$name][output]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (@$array["output"] > 0)
		));
		
		$this->addInput($key . "_uri",array(
			"name" => "object[$name][uri]",
			"value" => $array["uri"]
		));
		
		$this->addLink($key . "_url",array(
			"link" => $this->getUrl() . $array["uri"],
			"visible" => (@$array["output"] > 0)
		));
		
		$this->addLink($key . "_xml_link",array(
			"link" => $this->xmlLink . "?uri=" . $array["uri"],
			"visible" => (@$array["output"] > 0)
		));
		
		$this->addInput($key . "_title",array(
			"name" => "object[$name][title]",
			"value" => @$array["title"]
		));
		
		$this->addCheckbox($key . "_output_excerpt",array(
			"name" => "object[$name][output_type]",
			"value" => "excerpt",
			"selected" => @$array["output_type"] == "excerpt"
		));
		
		$this->addCheckbox($key . "_output_all",array(
			"name" => "object[$name][output_type]",
			"value" => "all",
			"selected" => @$array["output_type"] == "all"
		));
		
		
		$this->addInput($key . "_excerpt_size",array(
			"name" => "object[$name][excerpt_size]",
			"value" => (int)@$array["excerpt_size"],
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/" . __CLASS__  . ".html";
	}
	
	
	
}
?>