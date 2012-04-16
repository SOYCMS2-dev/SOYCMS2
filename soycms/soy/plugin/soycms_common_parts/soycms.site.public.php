<?php
/*
 * HTMLに色々追加する便利系
 */
class SOYCMS_CommonPartsExtension extends SOYCMS_SitePublicCommonExtension{
	
	/**
	 * 実行
	 */
	function execute($htmlObj,$page){
		
		$pageObj = $page->getPageObject();
		
		
		$pages = SOYCMS_DataSets::load("site.page_mapping",array());
		$uri_mappings = SOYCMS_DataSets::load("site.url_mapping",array());
		$mappings = SOYCMS_DataSets::load("site.page_tree_path",array());
		$tree = array();
		
		
		//トップページは表示しないでOK
		if($page->getUri() != "index.html"){
			
			$tree = (isset($mappings[$page->getId()])) ? $mappings[$page->getId()] : array();
			
			//index.htmlを見に行く様にする
			//自分がindex.html or page だった時
			$tmp = array();
			$new_pages = $pages;
			foreach($tree as $pageId){
				$uri = $pages[$pageId]["uri"];
				$index_uri = soycms_union_uri($uri,"index.html");
				
				// hogehoge/index.htmlがあった場合はそこへのリンク
				if(isset($uri_mappings[$index_uri])){
					$indexPageId = $uri_mappings[$index_uri];
					$tmp[] = $indexPageId;
					$new_pages[$indexPageId]["uri"] = str_replace("index.html","",$pages[$indexPageId]["uri"]);
				
				// hogehoge
				}else if(strpos($uri,"index.html")===false){
					$tmp[] = $pageId;
				}
			}
			
			//再代入
			$tree = $tmp;
			$pages = $new_pages;
			
			if($page->isDirectory()){
				
				//記事のURLに「/」が含まれる場合はラベルがあることを期待する
				$args = $htmlObj->getArguments();
				if(count($args) > 1){
					$dirId = $page->getId();
					$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
					$alias = rawurldecode(implode("/",array_slice($args,0,count($args)-1)));
					try{
						$label = $labelDAO->getByParams($dirId,$alias);
						$tree[] = ":label";
						$pages[":label"] = array(
							"uri" => soycms_union_uri($page->getUri(),$label->getAlias()),
							"name" => $label->getName()
						);
					}catch(Exception $e){
						
					}
				}
				
				//親記事があれば
				$entry = SOY2DAO::find("SOYCMS_Entry", SOYCMS_Helper::get("entry_id"));
				if($entry->getParent() > 0){
					try{
						$parent = SOY2DAO::find("SOYCMS_Entry", $entry->getParent());
						$tree[] = ":parent";
						$pages[":parent"] = array(
							"uri" => soycms_union_uri($page->getUri(),$parent->getUri()),
							"name" => $parent->getTitle()
						);
					}catch(Exception $e){
						
					}
				}
				
				
				$tree[] = ":end";
				$pages[":end"] = array(
					"name" => $htmlObj->getEntryTitle()
				);
			}
			
			if($htmlObj instanceof SOYCMS_ListPageBase && $htmlObj->getLabel()){
				$tree[] = ":end";
				$pages[":end"] = array(
					"name" => $htmlObj->getLabel()->getName()
				);
			}
		}
		
		$htmlObj->addModel("bread_crumbs_wrap",array(
			"visible" => count($tree) > 0 && count($pages) > 0,
			"soy2prefix" => "cms"
		));
		
		$htmlObj->createAdd("bread_crumbs","SOYCMS_CommonPartsExtension_TopicPath",array(
			"list" => $pages,
			"treeIds" => $tree,
			"soy2prefix" => "cms",
			"childSoy2Prefix" => "cms"
		));
		
		//current_url
		$htmlObj->addLabel("current_url",array(
			"text" => soycms_get_page_url(implode("/",$htmlObj->getArguments())),
			"soy2prefix" => "cms"
		));
		
	}
}

PluginManager::extension("soycms.site.public.common_execute","soycms_common_parts","SOYCMS_CommonPartsExtension");

class SOYCMS_CommonPartsEntryListComponent extends SOYCMS_EntryListComponent{
	
	function populateItem($entity,$key){
		
		$this->createAdd("label_entry_title","HTMLLabel",array(
			"html" => $entity->getTitle(),
			"soy2prefix" => "cms"
		));
		
		$mappings = self::getMapping();
		$link = (isset($mappings[$entity->getDirectory()])) ? soycms_get_page_url($mappings[$entity->getDirectory()]["uri"],rawurldecode($entity->getUri())) : "";
		$link = preg_replace('/\/index\.html$/',"/",$link);
		
		$this->createAdd("label_entry_link","HTMLLink",array(
			"link" => $link,
			"soy2prefix" => "cms"
		));
		
		return parent::populateItem($entity,$key);
	}
}

class SOYCMS_CommonPartsExtension_TopicPath extends HTMLTree{
	private $siteLink;
	
	function init(){
		$this->siteLink = soycms_get_site_url();
	}
	function populateItem($entity,$key,$depth,$isLast){
		if(@$entity["uri"] == "_home")@$entity["uri"] = "";
		
		$this->addLink("page_link",array("link"=>
			(!$isLast) ? soycms_union_uri($this->siteLink, @$entity["uri"]): ""
		));
		
		$this->addLabel("page_name",array(
			"html" => ($isLast) ? "<strong>" . @$entity["name"]. "</strong>" : @$entity["name"]
		));
	}
}
