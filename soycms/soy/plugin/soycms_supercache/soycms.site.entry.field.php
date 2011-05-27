<?php

class SOYCMS_SuperCacheCustomField extends SOYCMS_EntryCustomFieldBase{
	
	function SOYCMS_SuperCacheCustomField(){
		include_once(dirname(__FILE__) . "/inc.php");
	}

	/**
	 * @return string
	 */
	function getForm(SOYCMS_Entry $entry){
		
		$dirUrl = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory())->getUri();
		$cache_file = soycms_supercache_get_directory($dirUrl, $entry->getUri());
		
		$html = '<div>';
		
		$html .= '<div class="label">' .
					'<h4>キャッシュの情報</h4>' .
				'</div>';
			
		$html .= '<div class="item">';
		if(!file_exists($cache_file)){
			$html .= "キャッシュは作成されていません";
		}else{
			$html .= date("Y-m-d H:i:s",filemtime($cache_file));
			$html .= "に作成されました";
			$html .= " <input id=\"clear_cache\" type=\"checkbox\" name=\"clear_cache\" value=\"1\" />" .
					"<label for=\clear_cache\"\">キャッシュを削除する</label>";
		}
		$html .= "</div>";
			
		$html .= "</div>";
		
		
		return $html;
	}

	/**
	 * doPost
	 */
	function doPost(SOYCMS_Entry $entry){
		$config = SOYCMS_DataSets::get("soycms_supercache.config",array());
		//キャッシュの削除
		if(isset($_POST["clear_cache"])){
			$dirUrl = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory())->getUri();
			$cache_file = soycms_supercache_get_directory($dirUrl, $entry->getUri());
			soy2_delete_dir($cache_file);
		}else{
			$dirUrl = SOY2DAO::find("SOYCMS_Page",$entry->getDirectory())->getUri();
			
			switch(@$config["cache_type"]){
				case "clear":
					soycms_supercache_clear();
					break;
				case "directory":
					soycms_supercache_clear_uri($dirUrl);
					break;
				case "entry":
					soycms_supercache_clear_uri(soycms_union_uri($dirUrl,$entry->getUri()));
					break;
			}
			
		}
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj,SOYCMS_Entry $entry,$mode = "list"){
		//do nothing
	}

	/**
	 * @onDelete
	 */
	function onDelete($id){


	}

}
PluginManager::extension("soycms.site.entry.field","soycms_supercache","SOYCMS_SuperCacheCustomField");