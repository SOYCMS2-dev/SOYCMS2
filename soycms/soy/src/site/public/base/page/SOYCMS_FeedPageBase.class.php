<?php

class SOYCMS_FeedPageBase extends SOYCMS_SitePageBase{
	
	private $directory;
	
	function SOYCMS_FeedPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}

	function build($args){
		
		$page = $this->getPageObject();
		$obj = $page->getPageObject();
		$this->directory = SOY2DAO::find("SOYCMS_Page",$page->getParent());
		
		$config = $page->getConfigObject();
		$d_config = $this->directory->getConfigObject();
		
		$this->addLabel("site_url",array(
			"text" => soycms_get_site_url(true),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("page_url",array(
			"text" => soycms_get_page_url($page->getUri()),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("site_description",array(
			"text" => SOYCMS_DataSets::load("site_description",""),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("directory_description",array(
			"text" => @$d_config["description"],
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("directory_description",array(
			"text" => @$d_config["description"],
			"soy2prefix" => "cms"
		));
		
		$entries = $this->getEntries($page,$obj);
		
		$this->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => $entries,
			"soy2prefix" => "block",
			"summary" => ($obj->getType() == "excerpt") ? $obj->getExcerpt() : -1
		));
		
		$this->createAdd("feed_last_update_date","DateLabel",array(
			"text" => (count($entries) > 0) ? array_shift($entries)->getCreateDate() : $this->getPageObject()->getUpdateDate(),
			"soy2prefix" => "cms"
		));
			
	}
	
	function getEntries($page,$obj){
		$dirId = $page->getParent();
		
		//子ディレクトリを全て対象とする
		$mapping = SOYCMS_DataSets::load("site.page_mapping");
		$urls = SOYCMS_DataSets::load("site.url_mapping");
		
		$directories = array($dirId);
		$dirUrl = $mapping[$dirId]["uri"];
		
		foreach($urls as $url => $id){
			if(strpos($url,$dirUrl) !== false || $dirUrl == "_home"){
				if($mapping[$id]["type"] == "detail"){
					$directories[] = $id;		
				}
				continue;
			}
			break;
		}
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setMode("open");
		$dao->setLimit($obj->getLimit());
		$dao->setOrder("create_date desc");
		
		return $dao->searchFeedEntryByDirectories($directories);
		
	}

}
?>