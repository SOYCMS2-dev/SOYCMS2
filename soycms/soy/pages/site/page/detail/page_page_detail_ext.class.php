<?php
SOY2HTMLFactory::importWebPage("page.page_page_detail");
/**
 * @title ディレクトリの設定 > 拡張
 */
class page_page_detail_ext extends page_page_detail{
	
	private $extId = null;
	
	function doPost(){
		
		//plugin
		PluginManager::load("soycms.site.page.config",$this->extId);
		PluginManager::invoke("soycms.site.page.config",array(
			"page" => $this->page,
			"mode" => "post"
		));
		
		$this->jump("page/detail/ext/" . $this->id . "/" . $this->extId . "?updated");
	}
	
	function page_page_detail_ext($args){
		
		$this->id = @$args[0];
		$this->arg = @$args[1];
		$this->extId = $this->arg;
		
		WebPage::WebPage();
			
		$this->buildTab();
		$this->buildPage();
		$this->bulidForm();
		
	}
	
	function buildPage(){
		
		PluginManager::load("soycms.site.page.config",$this->extId);
		$delegetor = PluginManager::invoke("soycms.site.page.config",array(
			"page" => $this->page,
			"mode" => "page"
		));
		
		$this->addLabel("ext_title",array("html" => $delegetor->getTitle()));
		$this->addLabel("ext_content",array("html" => $delegetor->getPage()));
		
		parent::buildPage();
	}
	
	function bulidForm(){
		$this->addForm("form");
		
	}
	
}