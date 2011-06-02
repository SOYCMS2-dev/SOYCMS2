<?php
/*
 * 記事検索用
 */
class SOYCMS_SimpleSearchExtension extends SOYCMS_SearchExtension{

	/**
	 * 検索実行
	 */
	function doSearch($page,$limit,$offset){
		
		$word = (isset($_GET["q"])) ? $_GET["q"] : "";
		$result = array();
		
		if(strlen($word) > 0){
			$wheres = array();
			$binds = array(
				":publish" => 1,
				":status" => "open"
			);
			
			$words = explode(" ",$word);
			foreach($words as $key => $text){
				$wheres[] = "(title LIKE :text{$key} OR content LIKE :text{$key})";
				$binds[":text{$key}"] = "%" . $text . "%";
			}
			
			$and = array();
			$and[] = "soycms_site_entry.entry_publish = :publish";
			$and[] = "soycms_site_entry.entry_status = :status";
			$and[] = "soycms_site_page.page_type NOT LIKE '.%'";
		
			$db = SOY2DAOFactory::create("SOYCMS_EntryDAO");
			$db->setLimit($limit);
			$db->setOffset($offset);
			
			$query = new SOY2DAO_Query();
			$query->table = "soycms_site_entry left outer join soycms_site_page on(soycms_site_entry.directory = soycms_site_page.id)";
			$query->sql = "soycms_site_entry.*";
			$query->prefix = "select";
			
			$query->order = "soycms_site_entry.update_date desc";
			$query->where = implode(" AND ", $and);
			if(count($wheres) > 0){
				$query->where .= " AND (" . implode(" OR ", $wheres) . ")";
			}
			
			$res = $db->executeOpenEntryQuery($query,$binds);
			
			foreach($res as $row){
				$result[] = $db->getObject($row);
			}
			
			
			//合計を取得
			$query->sql = "count(soycms_site_entry.id) as total_count";
			$db->setLimit(null);
			$db->setOffset(null);
			$res = $db->executeOpenEntryQuery($query,$binds);
			$total = (count($res)>0) ? $res[0]["total_count"] : 0;
			
			$this->setResult($result);
			$this->setTotal($total);
		
		}else{
			$this->setIsError(true);
		}
		
		//要素を設定
		$page->createAdd("search_text","HTMLLabel",array(
			"text" => '"' . $word . '"の検索結果',
			"soy2prefix" => "cms",
			"visible" => (strlen($word) > 0)
		));
		
	}
	
	/**
	 * 検索モジュールのカスタマイズ画面
	 */
	function getConfigForm($page){
		$html = array();
		$html[] = "<p class='intro'>検索フォームは以下を参考にしてください。</p>";
		$html[] = "<textarea class='m-area liq-area' rows=\"5\">";
		$html[] = htmlspecialchars('<form method="get" action="'.soycms_get_page_url($page->getUri()).'">');
		$html[] = htmlspecialchars('<input type="text" name="q" value="" />');
		$html[] = htmlspecialchars('<input type="submit" value="検索" />');
		$html[] = htmlspecialchars('</form>');
		$html[] = "</textarea>";
		
		
		return implode("\n",$html);
	}
	
	/**
	 * 検索モジュール名称
	 */
	function getTitle(){
		return "【SOYCMS】全文検索プラグイン";
	}
		
}

PluginManager::extension("soycms.search","soycms_simple_search","SOYCMS_SimpleSearchExtension");

