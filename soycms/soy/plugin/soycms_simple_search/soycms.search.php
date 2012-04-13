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
		$dir = null;	//ディレクトリで絞込み（設定）
		$exDir = null;	//除外ディレクトリ（設定）
		$result = array();
		
		$pageObject = $page->getPageObject()->getPageObject();
		if($pageObject instanceof SOYCMS_SearchPage){
			$config = $pageObject->getModuleConfig();
			if(isset($config["dir"]))$dir = $config["dir"];
			$dir = explode("\n",str_replace("*","%",$dir));
			
			if(isset($config["exdir"]))$exDir = $config["exdir"];
			$exDir = explode("\n",str_replace("*","%",$exDir));
		}
		
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
			
			if($dir){
				$dirWheres = array();
				foreach($dir as $key => $_dir){
					$_dir = trim($_dir);
					if(!$_dir)continue;
					
					$binds[":url" . $key] = $_dir;
					$dirWheres[] = "uri LIKE :url" . $key;
				}
				if($dirWheres){
					$and[] = "soycms_site_entry.directory IN (select id from soycms_site_page where ".implode(" OR ", $dirWheres).")";
				}
				
			}
			if($exDir){
				$dirWheres = array();
				foreach($exDir as $key => $_dir){
					$_dir = trim($_dir);
					if(!$_dir)continue;
					
					$binds[":exurl" . $key] = $_dir;
					$dirWheres[] = "uri LIKE :exurl" . $key;
				}
				if($dirWheres){
					$and[] = "soycms_site_entry.directory NOT IN (select id from soycms_site_page where ".implode(" OR ", $dirWheres).")";
				}
			}
		
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
		$config = $page->getObject()->getModuleConfig();
		
		$html = array();
		$html[] = "<p class='intro'>検索フォームは以下を参考にしてください。</p>";
		$html[] = "<div class=\"break\"><textarea class='m-area liq-area' rows=\"5\">";
		$html[] = htmlspecialchars('<form method="get" action="'.soycms_get_page_url($page->getUri()).'">');
		$html[] = htmlspecialchars('<input type="text" name="q" value="" />');
		$html[] = htmlspecialchars('<input type="submit" value="検索" />');
		$html[] = htmlspecialchars('</form>');
		$html[] = "</textarea></div>";
		
		$siteUrl = SOYCMS_SITE_URL;
		$dir = @$config["dir"];
		
		$html[] = "<div class=\"break\">";
		$html[] = "<h4>検索対象</h4>";
		$html[] = "<p class='intro'>検索対象のディレクトリのURLを指定してください。改行で複数指定出来ます。</p>";
		$html[] = '<p class="intro">例）newsのみ=「news」サブディレクトリのみ＝「news/*」news+サブディレクトリ＝「news*」</p>';
		$html[] = '<span style="vertical-align:top;">'.$siteUrl.'</span> <textarea name="object[moduleConfig][dir]" class="m-area" rows="4">'.htmlspecialchars($dir).'</textarea>';
		$html[] = "</div>";
		
		
		$dir = @$config["exdir"];
		
		$html[] = "<div class=\"break\">";
		$html[] = "<h4>検索から除く</h4>";
		$html[] = "<p class='intro'>検索対象から取り除くディレクトリのURLを指定してください。改行で複数指定出来ます。</p>";
		$html[] = '<p class="intro">例）newsのみ=「news」サブディレクトリのみ＝「news/*」news+サブディレクトリ＝「news*」</p>';
		$html[] = '<span style="vertical-align:top;">'.$siteUrl.'</span> <textarea name="object[moduleConfig][exdir]" class="m-area" rows="4">'.htmlspecialchars($dir).'</textarea>';
		
		$html[] = "</div>";
		
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

