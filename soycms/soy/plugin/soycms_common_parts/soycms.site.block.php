<?php
/*
 * Created on 2011/01/16
 * Block Extensions ランダムな記事を表示
 */
class SOYCMS_CommonRandomEntryBlock extends SOYCMS_BlockExtension{
	
	private $directories = array();
	
	/**
	 * 記事の取得
	 */
	function getEntries($from,$to){
		$limit = null;
		if($to){
			$limit = $to - (int)$from; 
		}
		
		$dirs = array();
		foreach($this->directories as $dir){
			if($dir && is_numeric($dir)){
				$dir = (int)$dir;
				$dirs[] = $dir;
			}
		}
		
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$sql = new SOY2DAO_Query;
		$sql->prefix = "select";
		$sql->table = "soycms_site_entry";
		if(!empty($dirs)){
			$sql->where = "directory in (".implode(",",$dirs).")";
		}
		
		//idの最小と最大を取得
		$sql->sql = "max(id) as max_id,min(id) as min_id,count(id) as total_entry";
		$res = $dao->executeOpenEntryQuery($sql,array());
		$max = $res[0]["max_id"];
		$min = $res[0]["min_id"];
		$total = $res[0]["total_entry"];
		
		if($limit){
			$limit = min($total,$limit);
		}else{
			$limit = $total;
		}
		
		$res = array();
		$range = range($min,$max);
		array_rand($range);
		
		
		$dirs = $this->directories;
		$counter = 0;
		foreach($range as $random_id){
			try{
				$obj = $dao->getById($random_id);
				
				if(empty($dirs) || in_array($obj->getDirectory(),$dirs)){
					$res[$obj->getId()] = $obj;
				}
			}catch(Exception $e){
				
			}
			
			if(count($res)>=$limit)break;
			$counter++;
			
			//システム負荷のため100回でやめる
			if($counter >= 100)break;
		}
		
		return $res;	
	}
	
	/**
	 * ブロックのカスタマイズ画面
	 */
	function getConfigForm($page){
		
		//ディレクトリを全て
		$directories = SOY2DAO::find("SOYCMS_Page",(array("type" => "detail")));
		$values = $this->getDirectories();
		
		$html = array();
		
		$html[] = "<table class=\"form-table\">";
		$html[] = "<tr class=\"options select_directory\">";
		$html[] = "	<th>ディレクトリの指定</th>";
		$html[] = "	<td>";
		$html[] = "		記事ディレクトリを選択してください（複数可）。<br />";
		$html[] = '<input type="hidden" name="object[config][directories]" value="" />';
		$html[] = '	<select name="object[config][directories][]" size="5" multiple="1">';
		foreach($directories as $dir){
			$checked = (in_array($dir->getId(),$values)) ? "selected" : "";
			$html[] =  '<option value="'.$dir->getId().'" '.$checked.'>'.$dir->getName().'</option>';
		}
		$html[] = "</select>";
		$html[] = "	</td>";
		$html[] = "</tr>";
		$html[] = "</tr>";
		$html[] = "</table>";
		
		return implode("\n",$html);
	}
	
	/**
	 * 検索モジュール名称
	 */
	function getTitle(){
		return "【標準】記事をランダムで表示";
	}


	function getDirectories() {
		return $this->directories;
	}
	function setDirectories($directories) {
		if(!is_array($directories))$directories = array();
		$this->directories = $directories;
	}
}
PluginManager::extension("soycms.site.block","soycms_common_parts","SOYCMS_CommonRandomEntryBlock");
