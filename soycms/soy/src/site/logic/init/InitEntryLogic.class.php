<?php
/**
 *  記事とページを初期化する
 */
class InitEntryLogic extends SOY2LogicBase{
	
	function generateEntries($site,$config){
			$mappings = SOYCMS_DataSets::get("site.url_mapping");
			$type = $config["template"];
			
			$dao = new SOY2DAO();
			
			$base = new SOYCMS_Entry();
			$base->setPublish(1);
			$base->setStatus("open");
			
			//newsに記事追加
			$dir = $mappings["news"];
			$entry = clone($base);
			$entry->setTitle("お知らせ１");
			$entry->setContent("<p>お知らせ１です<br />お知らせ１です<br />お知らせ１です<br /></p>");
			$entry->setDirectory($dir);
			$entry->setUri("info".date("Ymd").".html");
			$entry->setOrder(0);
			$entry->save();
			
			$entry = clone($base);
			$entry->setTitle("お知らせ２");
			$entry->setContent("<p>お知らせ２</p>");
			$entry->setDirectory($dir);
			$entry->setUri("info".date("Ymd",strtotime("yesterday")).".html");
			$entry->setOrder(1);
			$entry->save();
			
			if($type == 0){
			//コンテンツAに記事追加
			$dir = $mappings["catalog"];
			$entry = clone($base);
			$entry->setDirectory($dir);
			for($i=1;$i<9;$i++){
			$entry->setId(null);
			$entry->setTitle("カタログの記事{$i}");
			$entry->setContent("<p>カタログ{$i}</p>");
			$entry->setUri("entry-{$i}.html");
			$entry->setOrder($i);
			$entry->save();
			}
			
			}
		}
		
	function generatePages($site,$config){
		$logic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
		$type = $config["template"];
		
		$templates = array(
			"_default/",
			"_default/",
			"_blank/"
		); 
		$template = $templates[$type];
		
		//HOME
		$page = new SOYCMS_Page();
		$page->setName("HOME");
		$page->setUri("_home");
		$page->setType("detail");
		$page->setTemplate("{$template}detail");
		$page->setConfigParam("public",1);
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi1"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$object = $page->getPageObject();
		$feed = $object->getFeedRDF();
		$feed["title"] = "#SiteName# - RSS1.0";
		$object->setFeedRDF($feed);
		
		$feed = $object->getFeedRSS();
		$feed["title"] = "#SiteName# - RSS2.0";
		$object->setFeedRSS($feed);
		
		$feed = $object->getFeedAtom();
		$feed["title"] = "#SiteName# - Atom";
		$object->setFeedAtom($feed);
		
		$object->save();
		
		//トップページ
		$page = new SOYCMS_Page();
		$page->setName("トップページ");
		$page->setUri("index.html");
		$page->setType("list");
		$page->setConfigParam("public",1);
		$page->setConfigParam("title","#SiteName#");
		$page->setTemplate("{$template}index");
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi1"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		//記事ディレクトリ
		$page = new SOYCMS_Page();
		$page->setName("お知らせ");
		$page->setUri("news");
		$page->setType("detail");
		$page->setConfigParam("public",1);
		$page->setTemplate("{$template}detail");
		$page->save();
		$pageId = $page->getId();
		$page->setProperties(array("BODY_CLASS" => "navi3"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$page = new SOYCMS_Page();
		$page->setName("お知らせ");
		$page->setUri("news/index.html");
		$page->setType("list");
		$page->setConfigParam("public",1);
		$page->setTemplate("{$template}list");
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi3"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$obj = $page->getObject();
		$obj->setDirectory($pageId);
		$obj->save();
		
		if($type == 0){
			//コンテンツA
			$page = new SOYCMS_Page();
			$page->setName("カタログ");
			$page->setUri("catalog");
			$page->setType("detail");
			$page->setConfigParam("public",1);
			$page->setTemplate("{$template}detail");
			$page->save();
			$pageId = $page->getId();
			$page->setProperties(array("BODY_CLASS" => "navi2"));
			$logic->generatePageClass($page);
			$logic->updatePageObject($page);
			
			$page = new SOYCMS_Page();
			$page->setName("カタログ");
			$page->setUri("catalog/index.html");
			$page->setType("list");
			$page->setConfigParam("public",1);
			$page->setTemplate("{$template}list_a");
			$page->save();
			$page->setProperties(array("BODY_CLASS" => "navi2"));
			$logic->generatePageClass($page);
			$logic->updatePageObject($page);
			
			$obj = $page->getObject();
			$obj->setDirectory($pageId);
			$obj->save();
			
			//お問い合わせ
			$page = new SOYCMS_Page();
			$page->setName("お問い合わせ");
			$page->setUri("contact");
			$page->setType("app");
			$page->setConfigParam("public",1);
			$page->setConfigParam("icon","mail.gif");
			$page->setTemplate("{$template}inquiry");
			$page->save();
			$page->setProperties(array("BODY_CLASS" => "navi5"));
			$logic->generatePageClass($page);
			$logic->updatePageObject($page);
			
			$obj = $page->getObject();
			$obj->setApplicationId("soycms_simple_form");
			$obj->save();
			
			//検索
			$page = new SOYCMS_Page();
			$page->setName("検索");
			$page->setUri("search");
			$page->setType("search");
			$page->setConfigParam("public",1);
			$page->setTemplate("{$template}search");
			$page->save();
			$page->setProperties(array("BODY_CLASS" => ""));
			$logic->generatePageClass($page);
			$logic->updatePageObject($page);
			
			$obj = $page->getObject();
			$obj->setModule("soycms_simple_search");
			$obj->save();
			
		}
		
		//エラーページ
		$page = new SOYCMS_Page();
		$page->setName("403 Forbidden");
		$page->setUri("403.html");
		$page->setType(".error");
		$page->setConfigParam("public",1);
		$page->setConfigParam("title","#SiteName#");
		$page->setTemplate("{$template}error");
		$obj = $page->getObject();
		$obj->setStatusCode(403);
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi1"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$obj->save();
		
		$page = new SOYCMS_Page();
		$page->setName("404 Not Found");
		$page->setUri("404.html");
		$page->setType(".error");
		$page->setConfigParam("public",1);
		$page->setConfigParam("title","#SiteName#");
		$page->setTemplate("{$template}error");
		$obj = $page->getObject();
		$obj->setStatusCode(404);
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi1"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$obj->save();
		
		$page = new SOYCMS_Page();
		$page->setName("500 Internal Server Error");
		$page->setUri("500.html");
		$page->setType(".error");
		$page->setConfigParam("public",1);
		$page->setConfigParam("title","#SiteName#");
		$page->setTemplate("{$template}error");
		$obj = $page->getObject();
		$obj->setStatusCode(500);
		$page->save();
		$page->setProperties(array("BODY_CLASS" => "navi1"));
		$logic->generatePageClass($page);
		$logic->updatePageObject($page);
		
		$obj->save();
		
		$logic->updatePageMapping();
	}
		
		/**
	 * ブロック情報を作成
	 */
	function generateBlocks($site,$config){
		$mappings = SOYCMS_DataSets::get("site.url_mapping");
		$type = $config["template"];
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		
		$templates = array(
			"_default/",
			"_default/",
			"_blank/"
		); 
		$template = $templates[$type];
		
		
		$dir = $mappings["news"];
		$page = $dao->getById($dir);
		$newsblock = SOYCMS_Block::load("news",SOYCMS_Template::getTemplateDirectory() . $template . "index/block/");
		if($newsblock){
			$object = $newsblock->getObject();
			$object->setDirectoryType(1);
			$object->setDirectories(array($dir));
			$newsblock->setObject($object);
			$newsblock->setIndexUrl("#SiteUrl#news/");
			
			//index
			$newsblock->save();
			
			//list
			$newsblock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "list/block/news.block");
			$newsblock->save();
			
			if($type == 0){
			
				//detail
				$newsblock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "detail/block/news.block");
				$newsblock->save();
				
				//inquiry
				$newsblock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "inquiry/block/news.block");
				$newsblock->save();
				
				
				
				//list_a
				$newsblock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "list_a/block/news.block");
				$newsblock->save();
				
				//search
				$newsblock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "search/block/news.block");
				$newsblock->save();
			
			}
			
		}
		
		//catalogがあるとき
		if(isset($mappings["catalog"])){
			$catalogBlock = SOYCMS_Block::load("catalog",SOYCMS_Template::getTemplateDirectory() . $template . "index/block/");
			$dir = $mappings["catalog"];
			$page = $dao->getById($dir);
			if($catalogBlock){
				$object = $catalogBlock->getObject();
				$object->setDirectoryType(1);
				$object->setDirectories(array($dir));
				$catalogBlock->setObject($object);
				$catalogBlock->setIndexUrl("#SiteUrl#catalog/");
				
				//index
				$catalogBlock->save();
				
				//list
				$catalogBlock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "list/block/catalog.block");
				$catalogBlock->save();
				
				//detail
				$catalogBlock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "detail/block/catalog.block");
				$catalogBlock->save();
				
				//inquiry
				$catalogBlock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "inquiry/block/catalog.block");
				$catalogBlock->save();
				
				
				
				//list_a
				$catalogBlock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "list_a/block/catalog.block");
				$catalogBlock->save();
				
				//search
				$catalogBlock->setPath(SOYCMS_Template::getTemplateDirectory() . $template . "search/block/catalog.block");
				$catalogBlock->save();
			
			
				
			}	
		}
			
	}
}
?>