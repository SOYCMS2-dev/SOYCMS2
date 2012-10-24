<?php


/**
 * ラベルで設定
 */
class SOYCMS_Block_DirectoryBlockComponent extends SOYCMS_Block_BlockComponentBase{
	
	/**
	 * 一覧画面で表示
	 */
	function getPreview(){
		
		$logic = SOY2::createLogic("site.logic.entry.SOYCMS_EntrySearchLogic");
		$res = $logic->searchByDirectories($this->getDirectories());
		
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/DirectoryBlockComponentPreviewPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("DirectoryBlockComponentPreviewPage",array(
			"arguments" => $res,
			"directories" => $this->getDirectories()
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
		
	}
	
	/**
	 * 詳細画面で表示
	 */
	function getForm(){
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/DirectoryBlockComponentFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("DirectoryBlockComponentFormPage",array(
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
	 * 公開側で使用
	 */
	function execute($blockComponent,$htmlObj){
		
		//ラベル
		if($this->getIsGroupByLabel()){
			//現在のディレクトリを指定かつ、子ディレクトリは含まない場合
			if(($this->getDirectoryType() == 0 && $this->getIncludeChildDirectory())
				|| count($this->getDirectories()) == 1
			){
				
				$dirId = array_shift($this->getDirectories());
				
				//現在のディレクトリを取得
				if($this->getDirectoryType() == 0){
					$inst = SOYCMS_SiteController::getInstance();
					$dir = $inst->getDirectoryObject();
					$dirId = $dir->getId();
				}
				
				$mapping = SOYCMS_DataSets::load("site.page_mapping");
				$pageUrl = $mapping[$dirId]["uri"];
				
				//高速化対策、非表示の時は記事を取得しない
				$blockComponent->createAdd("label_list","SOYCMS_LabelListComponent",array(
					"list" => ($blockComponent->getVisible()) ? $this->getEntryLabels($dirId) : array(),
					"soy2prefix" => "cms",
					"dirUrl" => $pageUrl,
					"mode" => "block"
				));
				
				$blockComponent->addModel("entry_list",array(
					"soy2prefix" => "cms",
					"visible" => false
				));
				
				return;
			}
		}
		
		
		return parent::execute($blockComponent,$htmlObj);
	}
	
	/**
	 * 公開側で使用
	 */
	function getEntryLabels($dirId,$currentLabel = null){
		
		$entries = array();
		$directories = array($dirId);
		$tags = $this->getTags();
		$isAndTag = $this->getTagOption();
		
		//ラベルでグループの時はラベルの絞り込みは使えない
		$labels = array();
		$isAndLabel = true;
		
		//ラベルを全て取得
		$dao = SOY2DAOContainer::get("SOYCMS_LabelDAO");
		$cDao = SOY2DAOContainer::get("SOYCMS_ObjectCustomFieldDAO");
		
		//条件
		$conditions = $this->getLabelConditions();
		
		//ラベルの表示件数
		$limit = ($this->getLabelLimit()) ? $this->getLabelLimit() : null;
		
		//現在のラベルのみ表示する場合
		if($this->getIsGroupOnlyCurrentLabel()){
			try{
				$labelList = array(
					$dao->getById($currentLabel)
				);
			}catch(Exception $e){
				return array();
			}
		
		//通常はディレクトリで絞り込む
		}else{
			$labelList = array();
			$labels = $dao->getByDirectory($dirId);
		}
		
		$counter = 0;
		foreach($labels as $id => $label){
			$_labels = array($label->getId());
			$entries = $this->searchEntry($directories,$_labels,$isAndLabel,$tags,$isAndTag);
			if(count($entries)<1){
				continue;
			}
			
			//condition
			if(!empty($conditions) && !$this->checkLabelConditions($id,$conditions,$cDao)){
				continue;
			}
			
			
			$labelList[$id] = $label;
			$labelList[$id]->setEntries($entries);
			$counter++;
			if(!is_null($limit) && $counter >= $limit){
				break;
			}
			
		}
		return $labelList;
	}
	
	function checkLabelConditions($labelId,$conditions,$dao){
		$c_values = SOYCMS_ObjectCustomField::getValues("label",$labelId);
		$res = false;
		foreach($conditions as $condition){
			$fieldId = $condition["fieldId"];
			if(!isset($c_values[$fieldId])){
				continue;
			}
			if(@$condition["removed"]>0){
				continue;
			}
			
			//最後の要素が「表示する」の時は表示しない
			//「表示しない」の時は表示するを返す
			$res = ($condition["operation"] == "visible") ? false : true;
			
			//条件チェック
			$value = $condition["value"];
			$field = $c_values[$fieldId];
			if(is_array($field))$field = array_shift($field);
			switch($condition["condition"]){
				case "equal":
					if($value != $field->getValue())continue;
					break;
				case "not_equal":
					if($value == $field->getValue())continue;
					break;
				case "large":
					if($value >= $field->getValue())continue;
					break;
				case "small":
					if($value <= $field->getValue())continue;
					break;
				case "equal_large":
					if($value > $field->getValue())continue;
					break;
				case "equal_small":
					if($value < $field->getValue())continue;
					break;
				case "equal_text":
					if(strcmp($value,$field->getValue()) == -1)continue;
					break;
				case "like_text":
					if(strpos($value,$field->getValue()) == -1)continue;
					break;
				default:
					continue;
					break;
			}
			
			//条件に一致した場合
			return ($condition["operation"] == "visible") ? true : false;
			
		}
		return $res;
	}
	
	/**
	 * 公開側で使用
	 */
	function getEntries(){
		$directories = $this->getDirectories();
		$labels = $this->getLabels();
		$tags = $this->getTags();
		$isAndLabel = $this->getLabelOption();
		$isAndTag = $this->getTagOption();
		
		//現在のディレクトリを指定
		if($this->getDirectoryType() == 0){
			$inst = SOYCMS_SiteController::getInstance();
			$dir = $inst->getDirectoryObject();
			
			$directories = array(
				($dir) ? $dir->getId() : 0
			);
			
			//子ディレクトリを含むかどうか
			if($this->getIncludeChildDirectory()){
				$relation = SOYCMS_DataSets::load("site.page_relation");
				if(isset($relation[$dir->getId()])){
					$relation = $relation[$dir->getId()];
					if(!$this->getIncludeChildIndex()){
						$mapping = SOYCMS_DataSets::load("site.page_mapping");
						foreach($relation as $key => $id){
							if($mapping[$id]["type"] != "detail"){
								unset($relation[$key]);
							}
						}
						$relation = array_values($relation);
					}
					
					$directories = array_merge($directories,$relation);
				}
			}
		}
		
		return $this->searchEntry($directories,$labels,$isAndLabel,$tags,$isAndTag);
	}
		
	/**
	 * 検索実行
	 */
	function searchEntry($directories,$labels,$isAndLabel,$tags,$isAndTag){
		$entries = array();
		
		/* @var $searchLogic SOYCMS_EntrySearchLogic */
		$searchLogic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntrySearchLogic");
		
		//build query
		list($sql,$binds) = $searchLogic->buildSearchQuery($directories,$labels,$isAndLabel,$tags,$isAndTag);
		
		//build sort
		$sql->order = $this->buildSortQuery();
		
		//検索実行
		$dao = SOY2DAOContainer::get("SOYCMS_EntryDAO");
		if(!is_null($this->getCountFrom()) && $this->getCountFrom() > 0)$dao->setOffset($this->getCountFrom() - 1);
		if(!is_null($this->getCountTo()))$dao->setLimit($this->getCountTo() - max(0,$this->getCountFrom() - 1));
		$res = $dao->executeOpenEntryQuery($sql,$binds);
		
		//結果追加
		foreach($res as $row){
			$entries[] = $dao->getById($row["id"]);
		}
		
		//ディレクトリ一件指定 && 子を指定、子のindex.htmlを指定
		if(count($directories)==1 &&
			($this->getIncludeIndex() || $this->getIncludeChildIndex())){
			$entries = $this->appendIndexEntries($dao,$directories[0],$entries);
		}
		
		return $entries;
	}
	
	function appendIndexEntries($dao,$directoryId,$entries){
		$mapping = SOYCMS_DataSets::load("site.page_mapping");
		$url = SOYCMS_DataSets::load("site.url_mapping");
		$tmp = array();
		
		$sortType = $this->getIndexOrder();
		
		//ディレクトリのindex.htmlが挿入されているか
		$indexPushed = false;
		
		if($this->getIncludeIndex()){
			$uri = @$mapping[$directoryId]["uri"];
			if(isset($url[$uri . "/index.html"])){
				$indexId = $url[$uri . "/index.html"];
				try{
					$indexEntry = $dao->getPageEntry($indexId);
					if(!$indexEntry->isOpen())throw new Exception("");
					
					switch($sortType){
						case 1:
						case 2:
							array_unshift($entries,$indexEntry);
							break;
						case 3:
							array_push($entries,$indexEntry);
							break;
					}
					
					$indexPushed = true;
				}catch(Exception $e){
					
				}
			}
		}
		if($this->getIncludeChildIndex()){
			$uri = @$mapping[$directoryId]["uri"];
			$tmpEntries = array();
			
			foreach($mapping as $id => $array){
				if(strpos($array["uri"],$uri) === 0
				&& $array["type"] == "detail"
				&& $uri != $array["uri"]
				&& isset($url[$array["uri"] . "/index.html"])){
					
					$indexId = $url[$array["uri"] . "/index.html"];
					
					try{
						$indexEntry = $dao->getPageEntry($indexId);
						if(!$indexEntry->isOpen())continue;
					
						switch($sortType){
							case 2:
								$tmpEntries[] = $indexEntry;
								break;
							case 1:
							case 3:
								array_push($entries,$indexEntry);
								break;
						}
					}catch(Exception $e){
					}
				}
			}
			
			if($sortType == 2){
				if($indexPushed){
					$entries = array_merge(array($entries[0]),$tmpEntries,array_slice($entries,1));
				}else{
					$entries = array_merge($tmpEntries,$entries);
				}
			}
		}
		
		return $entries;
	}
	
	function buildSortQuery(){
		$order = "soycms_site_entry.update_date desc, id desc";
		
		switch($this->order){
			case "update_desc":
				$order = "soycms_site_entry.update_date desc, id desc";
				break;
			case "update":
				$order = "soycms_site_entry.update_date, id";
				break;
			case "create_desc":
				$order = "soycms_site_entry.create_date desc, id desc";
				break;
			case "create_desc":
				$order = "soycms_site_entry.create_date, id";
				break;
			case "label_order":
				$order = "soycms_site_label.display_order desc";
				break;
			case "directory_order":
				$order = "soycms_site_entry.display_order";
				break;
		}
		return $order;
	}
	
	function getSortTypes(){
		return array(
			"update_desc",
			"update",
			"create_desc",
			"create",
			"directory_order",
			"label_order"
		);
	}
	
	private $directories = array();
	private $labels = array();
	private $labelOption = 0;
	private $tags = array();
	private $tagOption = 0;
	private $order = "create_desc";
	private $countFrom;
	private $countTo = 5;
	private $includeChildDirectory = false;
	private $includeIndex = true;
	private $includeChildIndex = false;
	private $indexOrder = 1;
	private $directoryType = 0;
	private $isGroupByLabel = false;
	private $isGroupOnlyCurrentLabel = false;
	private $labelLimit = null;
	private $labelConditions = array();
	
	function onCreate(){
		
	}
	
	function onDelete(){
		
	}
	
	/* getter setter */
	
	

	function getDirectories() {
		return $this->directories;
	}
	function setDirectories($directories) {
		$this->directories = $directories;
	}
	function getLabels() {
		if(!is_array($this->labels))$this->labels = array();
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
	function getLabelOption() {
		return $this->labelOption;
	}
	function setLabelOption($labelOption) {
		$this->labelOption = $labelOption;
	}
	function getTags() {
		$this->tags = array_diff($this->tags,array(""));
		return $this->tags;
	}
	function setTags($tags) {
		if(!is_array($tags)){
			$tags = explode(" ",$tags);
		}
		$this->tags = $tags;
	}
	function getTagOption() {
		return $this->tagOption;
	}
	function setTagOption($tagOption) {
		$this->tagOption = $tagOption;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getCountFrom() {
		return (strlen($this->countFrom)>0) ? $this->countFrom : null;
	}
	function setCountFrom($countFrom) {
		$this->countFrom = $countFrom;
	}
	
	function getCountTo() {
		return (strlen($this->countTo)>0) ? $this->countTo : null;
	}
	function setCountTo($countTo) {
		$this->countTo = $countTo;
	}

	function getIncludeIndex() {
		return $this->includeIndex;
	}
	function setIncludeIndex($includeIndex) {
		$this->includeIndex = $includeIndex;
	}

	function getIncludeChildIndex() {
		return $this->includeChildIndex;
	}
	function setIncludeChildIndex($includeChildIndex) {
		$this->includeChildIndex = $includeChildIndex;
	}

	function getIndexOrder() {
		return $this->indexOrder;
	}
	function setIndexOrder($indexOrder) {
		$this->indexOrder = $indexOrder;
	}

	function getIncludeChildDirectory() {
		return $this->includeChildDirectory;
	}
	function setIncludeChildDirectory($includeChildDirectory) {
		$this->includeChildDirectory = $includeChildDirectory;
	}

	function getDirectoryType() {
		return $this->directoryType;
	}
	function setDirectoryType($directoryType) {
		$this->directoryType = $directoryType;
	}

	function getIsGroupByLabel() {
		return $this->isGroupByLabel;
	}
	function setIsGroupByLabel($isGroupByLabel) {
		$this->isGroupByLabel = $isGroupByLabel;
	}

	function getLabelLimit() {
		return $this->labelLimit;
	}
	function setLabelLimit($labelLimit) {
		$this->labelLimit = $labelLimit;
	}

	function getLabelConditions() {
		return $this->labelConditions;
	}
	function setLabelConditions($labelConditions) {
		$this->labelConditions = $labelConditions;
	}

	function getIsGroupOnlyCurrentLabel() {
		return $this->isGroupOnlyCurrentLabel;
	}
	function setIsGroupOnlyCurrentLabel($isGroupOnlyCurrentLabel) {
		$this->isGroupOnlyCurrentLabel = $isGroupOnlyCurrentLabel;
	}
}


?>