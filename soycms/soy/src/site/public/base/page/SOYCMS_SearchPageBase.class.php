<?php

class SOYCMS_SearchPageBase extends SOYCMS_SitePageBase{
	
	function SOYCMS_SearchPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}

	function build($args){
		$currentPage = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
		$page = $this->getPageObject();
		$searchPage = $page->getPageObject();
		$limit = $searchPage->getLimit();
		$offset = ($currentPage-1) * $limit;
		$total = 0;
		$entries = array();
		
		//検索実行
		$obj = $searchPage->getModuleObject();
		if($obj){
			$obj->doSearch($this,$limit,$offset);
			$total = $obj->getTotal();
			$entries = $obj->getResult();
		}
		
		$this->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => $entries,
			"soy2prefix" => "block",
			"visible" => $this->isItemVisible("default:entry_list"),
			"configLink" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/page/detail/" . $page->getId() . "#tab1/advance" : "",
		));
		
		$query = $_GET;
		if(isset($_GET["page"]))unset($query["page"]);
		
		$this->createAdd("pager","SOYCMS_ListPageBase_HTMLPager",array(
			"start" => ($currentPage - 1) * $limit + 1,
			"page" => $currentPage,
			"total" => $total,
			"limit" => $limit,
			"link" => soycms_get_page_url($page->getUri()) . "?" . http_build_query($query) . "&page=",
			"soy2prefix" => "block",
			"childPrefix" => "cms",
			"visible" => count($entries) > 0 && $this->isItemVisible("default:pager")
		));
		
		$this->addModel("no_result",array(
			"soy2prefix" => "cms",
			"visible" => count($entries) < 1
		));

		$this->addModel("has_result",array(
			"soy2prefix" => "cms",
			"visible" => count($entries) > 0
		));
		
		$this->addModel("search_success",array(
			"soy2prefix" => "cms",
			"visible" => ($obj) ? !$obj->getIsError() : false
		));
		
		$this->addModel("search_failed",array(
			"soy2prefix" => "cms",
			"visible" => ($obj) ? $obj->getIsError() : true
		));
		
		
	}

}

?>