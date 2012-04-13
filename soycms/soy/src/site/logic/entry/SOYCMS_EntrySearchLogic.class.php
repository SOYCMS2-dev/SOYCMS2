<?php
/**
 * 記事の検索
 */
class SOYCMS_EntrySearchLogic extends SOY2LogicBase{
	
	//期間指定
	private $createDateRange = array();	
	private $order = null;
	
	/**
	 * SOY2DAO_Queryの作成
	 * @param $directories
	 * @param $labels
	 * @param $isAndLabel
	 * @param $tags
	 * @param $isAndTag
	 * @return SOY2DAO_Query
	 */
	function buildSearchQuery($directories,$labels = array(),$isAndLabel = false,$tags = array(),$isAndTag = false){
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$sql = new SOY2DAO_Query();
		$binds = array();
		$sql->prefix = "select";
		$sql->sql = "id";
		$sql->table = "soycms_site_entry";
		
		
		$havingcount = array();
		
		$wheres = array();
		$havings = array();
		
		//1ページ目のみ
		$wheres[] = "soycms_site_entry.parent_entry_id is null";
		
		if(count($directories) > 0){
			$tmpWhere = array();
			foreach($directories as $directory){
				$binds[":directory" . count($tmpWhere)] = $directory;
				$tmpWhere[] = "soycms_site_entry.directory = :directory" . count($tmpWhere);
			}
			$wheres[] = "(". implode(" OR ",$tmpWhere) .")";
		}
		
		if(count($labels) > 0){
			$sql->table .= " left outer join soycms_site_entry_label on (soycms_site_entry_label.entry_id = soycms_site_entry.id)\n";
			
			$tmpWhere = array();
			foreach($labels as $label){
				$binds[":label" . count($tmpWhere)] = $label;
				$tmpWhere[] = "soycms_site_entry_label.label_id = :label" . count($tmpWhere);
			}
			$wheres[] = "(". implode(" OR ",$tmpWhere) .")";
			
			if($isAndLabel){
				$havings[] = "count(soycms_site_entry_label.label_id) >= " . count($labels);
			}
			
		}
		if(count($tags) > 0){
			$sql->table .= " left outer join soycms_site_tag on (soycms_site_tag.entry_id = soycms_site_entry.id)\n";
			
			$tmpWhere = array();
			foreach($tags as $tag){
				$binds[":tag" . count($tmpWhere)] = SOYCMS_Tag::convertHash($tag);
				$tmpWhere[] = "soycms_site_tag.hash_text = :tag" . count($tmpWhere);
			}
			$wheres[] = "(". implode(" OR ",$tmpWhere) .")";
			
			if($isAndTag){
				$havings[] = "count(soycms_site_tag.hash_text) >= " . count($tags);
			}
		}
		
		if(count($havings) > 0){
			if($isAndLabel && $isAndTag){
				$sql->having = "count(id) = " . count($tags) * count($labels);
			}else{
				$sql->having = implode(" AND ", $havings);
			}
		}
		
		if(!empty($this->createDateRange)){
			if(!empty($this->createDateRange[0])){
				$wheres[] = "soycms_site_entry.create_date >= :create_date_from";
				$binds["create_date_from"] = $this->createDateRange[0];
			}
			if(!empty($this->createDateRange[1])){
				$wheres[] = "soycms_site_entry.create_date <= :create_date_to";
				$binds["create_date_to"] = $this->createDateRange[1];
			}
		}
		
		$sql->group = "id";
		$sql->where = implode("\n AND " ,$wheres);
		
		return array($sql,$binds);
	}
	
	/**
	 * ラベルIDを複数指定して取得(or検索)
	 */
	function getByLabelIds($labelIds,$offset = null,$limit = null){
		$table = "soycms_site_entry";
		$labelTable = "soycms_site_entry_label";
		
		$res = array();
		$query = new SOY2DAO_Query();
		$binds = array();
	
		//build query
		$query->prefix = "select";
			$query->sql = "$table.*";
			$query->table =
				$table
				. " left outer join " .
				$labelTable
				. " on ($table.id = $labelTable.entry_id)";
		
		if(count($labelIds)){
			$query->where = "label_id in (". implode(",",$labelIds) . ")";
		}
			
		$dao = $this->getEntryDAO();
		$array = $dao->executeQuery($query,$binds);
		
		foreach($array as $row){
			$obj = $dao->getObject($row);
			$res[$obj->getId()] = $obj; 
		}
		
		return $res;
	}
	
	function searchByDirectories($directories,$limit = null,$offset = null){
		$ids = array();
		foreach($directories as $value){
			$ids[] = (int)$value;
		}
		if(empty($ids))return false;
		
		$dao = $this->getEntryDAO();
		$dao->setLimit(null);
		$dao->setOffset(null);
		$dao->setOrder(null);
		if($limit)$dao->setLimit($limit);
		if($offset)$dao->setOffset($offset);
		if($this->order)$dao->setOrder($this->order);
		
		return $this->getEntryDAO()->searchByDirectories($ids);
	}
	
	private $entryDAO;
	
	function getEntryDAO(){
		if(!$this->entryDAO){
			$this->entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		}
		
		return $this->entryDAO;
	}
	
	/* getter setter */

	function getCreateDateRange() {
		return $this->createDateRange;
	}
	function setCreateDateRange($createDateRange) {
		$this->createDateRange = $createDateRange;
	}

	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
}
?>