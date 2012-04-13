<?php

class SOYCMS_ListPage extends SOYCMS_PageBase{
	
	private $directory;
	private $isIncludeChild = false;
	private $periodFrom = null;
	private $periodTo = null;
	private $order = "directory_order";
	private $limit = 10;
	
	/* 2.0.7 */
	private $plugin = null;
	private $extension = array();
	
	/* 2.0.8 */
	private $targetUri = null;
	
	/**
	 * 検索する
	 */
	function getEntries($offset = null,$label = null, $tag = null){
		
		$entries = array();
		$searchLogic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntrySearchLogic");
		
		//ラベルで絞り込み
		$labels = array();
		if($label)$labels[] = $label;
		
		//タグで絞り込み
		$tags = array();
		if($tag)$tags[] = $tag;
		
		//日付で絞り込み
		if($this->periodFrom || $this->periodTo){
			$range = array($this->periodFrom,$this->periodTo);
			$searchLogic->setCreateDateRange($range);
		}
		
		//子も対象にするか
		$directories = array($this->directory);
		if($this->getIsIncludeChild()){
			$mapping = SOYCMS_DataSets::load("site.page_mapping");
			$urls = SOYCMS_DataSets::load("site.url_mapping");
			
			$flag = false;
			$dirUrl = "";

			foreach($urls as $url => $id){
				if(!$flag){
					$flag = ($id == $this->directory);
					if($flag)$dirUrl = $url;
					continue;
				}
				if(strpos($url,$dirUrl) !== false){
					if($mapping[$id]["type"] == "detail"){
						$directories[] = $id;		
					}
					continue;
				}
				break;
			}
		}

		//build query
		list($sql,$binds) = $searchLogic->buildSearchQuery($directories,$labels,false,$tags,false);
		
		switch($this->order){
			case "update_desc":
				$sql->order = "soycms_site_entry.update_date desc, id desc";
				break;
			case "update":
				$sql->order = "soycms_site_entry.update_date, id";
				break;
			case "create_desc":
				$sql->order = "soycms_site_entry.create_date desc, id desc";
				break;
			case "create_desc":
				$sql->order = "soycms_site_entry.create_date, id";
				break;
			case "directory_order":
				$sql->order = "soycms_site_entry.display_order";
				break;
		}
		
		$dao = SOY2DAOContainer::get("SOYCMS_EntryDAO");
		if($offset)$dao->setOffset($offset);
		if(strlen($this->getLimit())>0)$dao->setLimit($this->getLimit());
		$res = $dao->executeOpenEntryQuery($sql,$binds);
		
		foreach($res as $row){
			$entries[] = $dao->getById($row["id"]);
		}
		
		//合計件数を取得
		$query = new SOY2DAO_Query();
		$query->prefix = "select";
		$query->table = $sql->table;
		$query->sql = "count(id) as entry_count";
		$query->where = $sql->where;
		
		$dao->setOffset(null);
		$dao->setLimit(1);
		
		$total = $dao->executeOpenEntryQuery($query,$binds);
		$total = (count($total)>0) ? @$total[0]["entry_count"] : 0;
		
		return array($entries,$total);
	}
	
	/**
	 * ソート順
	 */
	function getSortTypes(){
		return array(
			"update_desc",
			"update",
			"create_desc",
			"create",
			"directory_order",
		);
	}
	
	/**
	 * 設定画面
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/ListPageFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("ListPageFormPage",array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 標準で追加されるブロックを表示
	 */
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事を表示",
			"entry_list" => "記事一覧を表示",
			"pager" => "ページャー",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
			"current_directory_label" => "選択済みのラベル"
		);
	}
	
	function getPluginObject($delegetor = null){
		if(!$delegetor){
			PluginManager::load("soycms.site.page.list");
			$delegetor = PluginManager::invoke("soycms.site.page.list",array("moduleId" => $this->plugin));
		}
		$module = $delegetor->getModule();
		SOY2::cast($module,$this->extension);
		return $module;
	}
	
	/* getter setter */

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	function getPeriodFrom() {
		return $this->periodFrom;
	}
	function setPeriodFrom($periodFrom) {
		if(is_array($periodFrom) && strlen($periodFrom[0]) > 0){
			$periodFrom = strtotime(implode(" ",$periodFrom));
		}
		
		if(is_numeric($periodFrom)){
			$this->periodFrom = $periodFrom;
		}else{
			$this->periodFrom = null;	
		}
	}
	function getPeriodTo() {
		return $this->periodTo;
	}
	function setPeriodTo($periodTo) {
		if(is_array($periodTo) && strlen($periodTo[0]) > 0){
			if(empty($periodTo[1]))$periodTo[1] = "23:59";
			$periodTo = strtotime(implode(" ",$periodTo));
		}
		
		if(is_numeric($periodTo)){
			$this->periodTo = $periodTo;
		}else{
			$this->periodTo = null;
		}
		
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}
	function getIsIncludeChild() {
		return $this->isIncludeChild;
	}
	function setIsIncludeChild($isIncludeChild) {
		$this->isIncludeChild = $isIncludeChild;
	}
	function getPlugin() {
		return $this->plugin;
	}
	function setPlugin($plugin) {
		$this->plugin = $plugin;
	}
	function getExtension() {
		return $this->extension;
	}
	function setExtension($extension) {
		$this->extension = $extension;
	}

	function getTargetUri() {
		return $this->targetUri;
	}
	function setTargetUri($targetUri) {
		$this->targetUri = $targetUri;
	}
}
