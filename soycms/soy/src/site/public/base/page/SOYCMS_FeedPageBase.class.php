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
			"mode" => "feed",
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
		$feed_config = SOYCMS_DataSets::load("site.feed_config", array());
		
		$directories = array($dirId);
		$dirUrl = $mapping[$dirId]["uri"];
		$isIncludeChild = $feed_config[$dirId]["child"];
		
		if($isIncludeChild){
			$relation = SOYCMS_DataSets::load("site.page_relation");
			$page_ids = $this->getDirectoryIds($dirId, $relation, $mapping, $feed_config);
			$directories = array_merge($directories, $page_ids);
		}
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$dao->setMode("open");
		$dao->setLimit($obj->getLimit());
		$dao->setOrder("create_date desc");
		
		return $dao->searchFeedEntryByDirectories($directories);
		
	}
	
	function getDirectoryIds($dirId, $list, $mapping, $feed_config, $incChildFlag = false){
		$pageIds = array();
		
		$dirUrl = $mapping[$dirId]["uri"];
		$child = $list[$dirId];
		
		if($dirUrl == "_home")$dirUrl = ".";
		
		foreach($child as $pageId){
			$type = $mapping[$pageId]["type"];
			if($type[0] ==".")continue;
			$pageUrl = $mapping[$pageId]["uri"];
			if($dirUrl != dirname($pageUrl))continue;
			
			if($type == "detail"){
				if($incChildFlag)continue;
				
				$isOutput = $feed_config[$pageId]["output"];
				if(!$isOutput)continue;
				
				$pageIds[] = $pageId;
				$incChild = $feed_config[$pageId]["child"];
				$pageIds = array_merge($pageIds,$this->getDirectoryIds($pageId, $list, $mapping, $feed_config, $incChild));
				
			}else{
				$pageIds[] = $pageId;
			}
		}
		
		return $pageIds;
	}

}
?>