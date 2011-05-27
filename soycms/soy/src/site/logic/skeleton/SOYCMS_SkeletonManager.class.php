<?php
SOY2::import("zip.SOYCMS_ZipHelper");
SOY2::import("admin.domain.SOYCMS_Skeleton");

class SOYCMS_SkeletonManager extends SOY2LogicBase{
	
	/**
	 * スケルトンの作成
	 */
	function exportSkeleton($filename,$config,$file = null){
		
		$helper = SOYCMS_ZipHelper::prepare(SOYCMS_ROOT_DIR . "tmp/".$filename.".zip");
		$dir = SOYCMS_ROOT_DIR . "tmp/" . $filename . "/";
		
		if(file_exists($dir))soy2_delete_dir($dir);
		soy2_mkdir($dir);
		
		if(!isset($config["Skeleton"]["information"])){
			$config["Skeleton"]["information"] = array();
		}
		
		//export contents
		$this->exportContents($filename . "/contents", $config);
		
		//export design
		$this->exportDesign($filename . "/design", $config);
		
		//export meta
		$this->exportSkeletonInfo($dir ."skeleton.xml",$config);
		
		//export thumbnail
		if($file && isset($file["tmp_name"]) && strlen($file["tmp_name"]) > 0){
			$ext = pathinfo($file["name"]);
			$ext = @$ext["extension"];
			move_uploaded_file($file["tmp_name"], SOYCMS_ROOT_DIR . "tmp/thumbnail." . $ext);
			
			soy2_resizeimage(
				SOYCMS_ROOT_DIR . "tmp/thumbnail." . $ext,
				$dir . "thumbnail.jpg",140,105
			);
		}
		
		$helper->add(".",$dir);
		
		$helper->compress();
		
		return $helper->getFileName();
	}
	
	/**
	 * スケルトンのimport
	 */
	function importSkeleton($target,$config = null){
		
		$this->uncompress(
			SOYCMS_ROOT_DIR . "content/skeleton/" . $target . "/contents.zip",
			SOYCMS_ROOT_DIR . "tmp/" . $target . "_contents"
		);
		
		$this->uncompress(
			SOYCMS_ROOT_DIR . "content/skeleton/" . $target . "/design.zip",
			SOYCMS_ROOT_DIR . "tmp/" . $target . "_design"
		);
		
		$this->importContents($target . "_contents");
		$this->importDesign($target . "_design");
		
	}
	
	/**
	 * スケルトンの情報の出力
	 */
	function exportSkeletonInfo($filepath,$config){
		$obj = SOY2::cast("SOYCMS_Skeleton",@$config["Skeleton"]);
		$obj->export($filepath);
	}

	/**
	 * @return filename
	 */
    function exportContents($filename, &$config){
    	
    	//zipを作成する
		$helper = SOYCMS_ZipHelper::prepare(SOYCMS_ROOT_DIR . "tmp/".$filename.".zip");
		
		$pages = SOY2DAO::find("SOYCMS_Page");
		
		//Temporary DAO
		$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
		if(file_exists(SOYCMS_ROOT_DIR . "tmp/contents/"))soy2_delete_dir(SOYCMS_ROOT_DIR . "tmp/contents/");
		
		soy2_mkdir(SOYCMS_ROOT_DIR . "tmp/contents/");
		$dsn = "sqlite:" . SOYCMS_ROOT_DIR . "tmp/contents/site.db";
		$pageDAO->setDsn($dsn);
		$entryDAO->setDsn($dsn);
		$labelDAO->setDsn($dsn);
		
		$sqls = explode(";",file_get_contents(SOY2::RootDir() . "site/logic/init/sql/sqlite.sql"));
		
		foreach($sqls as $sql){
			try{
				$pageDAO->executeUpdateQuery($sql);
			}catch(Exception $e){
				//do nothing
			}
		}
		
		
		$count = 0;
		$fieldDir = SOYCMS_SITE_DIRECTORY . ".field/";
		foreach($config["pages"] as $pageId){
			$dir = $pages[$pageId]->getPageDirectory();
			$helper->add(".pages/" . basename($dir), $dir);
			$pageDAO->insertId($pageId);
			$pageDAO->update($pages[$pageId]);
			$count++;
			
			//custom field ini
			if(file_exists($fieldDir . "entry-" .$pageId. ".ini")){
				$helper->add(".field/entry-" .$pageId. ".ini", $fieldDir . "entry-" .$pageId. ".ini");
			}
		}
		$pages = null;
		$config["Skeleton"]["information"]["directory_count"] = $count;
		
		$tmpDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		$count = 0;
		foreach($config["entries"] as $dirId){
			$entries = $tmpDAO->getByDirectory($dirId);
			foreach($entries as $entry){
				$entryDAO->insertId($entry->getId());
				
				$sections = $this->convertURL($entry->getSections());
				$content = $this->convertURL($entry->getContent());
				
				$entry->setSections($sections);
				$entry->setContent($content);
				
				$entryDAO->update($entry);
				
				if(isset($config["attachments"][$dirId])){
					$path = $entry->getAttachmentPath();
					$attachDir = str_replace(@SOYCMS_SITE_UPLOAD_DIR,"",$path);
					$attachDirName = str_replace(SOYCMS_SITE_DIRECTORY,"",$attachDir);
					$helper->add("files/" . $attachDirName,$path);
				}
				$count++;
				$config["Skeleton"]["information"]["contents_count"] = $count;
				
			}
			$entries = null;
		}
		
		//export all label
		$tmpDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
		$labels = $tmpDAO->get();
		foreach($labels as $label){
			$labelDAO->insertId($label->getId(),$label->getName());
			$labelDAO->update($label);
		}
		
		//add db
		$helper->add("contents/",SOYCMS_ROOT_DIR . "tmp/contents/");
		
		//圧縮実行
		$helper->compress();
		
		return $helper->getFileName();
    }
    
    /**
     * zipファイルの解凍
     */
    function uncompress($filepath,$targetDir){
    	$zipHelper = SOYCMS_ZipHelper::prepare($filepath);
		$zipHelper->setFilter(array($this,"restoreURLFromFile"));
		$zipHelper->uncompress($targetDir);
    }
    
    /**
     * コンテンツのインポート
     */
    function importContents($target,$config = null){
    	
    	$srcDir = SOYCMS_ROOT_DIR . "tmp/" . $target . "/";
		$pageFlag = false;	//ページの二重登録防止用
		
		if(!$config){
			$config["config"] = true;		//設定
			$config["attachments"] = true;	//添付ファイル
			$config["entries"] = true;		//記事
		}
		
		//Temporary DAO
		$dsn = "sqlite:" . $srcDir . "contents/site.db";
		$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
		$pageDAO->setDsn($dsn);
		$entryDAO->setDsn($dsn);
		$labelDAO->setDsn($dsn);
		
		//ページの設定
		if(isset($config["config"])){
			$this->overwrite(
				$srcDir . ".pages/",
				SOYCMS_SITE_DIRECTORY . ".page/"
			);
			
			$files = soy2_scandir($srcDir . ".field/");
			foreach($files as $file){
				$this->overwrite(
					$srcDir . ".field/" . $file,
					SOYCMS_SITE_DIRECTORY . ".field/" . $file
				);
			}
			
			$pages = $pageDAO->get();
			$newPageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
			
			foreach($pages as $page){
				
				//変更後のIDとURIが被っている場合はそのページを削除する
				try{
					$_check_uri_obj = $newPageDAO->getByUri($page->getUri());
					if($_check_uri_obj->getId() != $page->getId()){
						$_check_uri_obj->delete();
					}
				}catch(Exception $e){
					
				}
				
				try{
					$newPageDAO->getById($page->getId());						
				}catch(Exception $e){
					$newPageDAO->insertId($page->getId());
				}
				
				$newPageDAO->update($page);
			}
			
			$pages = null;
			$pageFlag = true;
		}
		
		//添付ファイル
		if(isset($config["attachments"])){
			$this->overwrite(
				$srcDir . "files/",
				SOYCMS_SITE_UPLOAD_DIR
			);
		}
		
		//記事とページ
		if(isset($config["entries"])){
			if(!$pageFlag){
				$pages = $pageDAO->get();
				$newPageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
				
				foreach($pages as $page){
					
					//変更後のIDとURIが被っている場合はそのページを削除する
					try{
						$_check_uri_obj = $newPageDAO->getByUri($page->getUri());
						if($_check_uri_obj->getId() != $page->getId()){
							$_check_uri_obj->delete();
						}
					}catch(Exception $e){
						
					}
					
					try{
						$newPageDAO->getById($page->getId());						
					}catch(Exception $e){
						$newPageDAO->insertId($page->getId());
					}
					
					$newPageDAO->update($page);
				}
				
				$pages = null;
			}
			
			//記事
			$entries = $entryDAO->get();
			$newEntryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
			
			$dirIds = array();	//空にしたディレクトリ
			
			foreach($entries as $entry){
				
				if(!in_array($entry->getDirectory(),$dirIds)){
					$dirIds[] = $entry->getDirectory();
					$newEntryDAO->deleteByDirectory($entry->getDirectory());
				}
				
				$sections = $this->restoreURL($entry->getSections());
				$content = $this->restoreURL($entry->getContent());
				
				$entry->setSections($sections);
				$entry->setContent($content);
				
				try{
					$newEntryDAO->getById($entry->getId());						
				}catch(Exception $e){
					$newEntryDAO->insertId($entry->getId());
				}
				$newEntryDAO->update($entry);
			}
			$entries = null;
			
			//label
			$labels = $labelDAO->get();
			$newLabelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
			foreach($labels as $label){
				$newLabelDAO->delete($label->getId());
				$newLabelDAO->insertId($label->getId(),$label->getName());
				$newLabelDAO->update($label);
			}
		}
    }
    
    /**
     * @return filename
     */
    function exportDesign($filename, &$config){
    	
    	//zipを作成する
		$helper = SOYCMS_ZipHelper::prepare(SOYCMS_ROOT_DIR . "tmp/".$filename.".zip");
		
		$templateDir = SOYCMS_Template::getTemplateDirectory();
		$count = 0;
		foreach(@$config["Template"] as $templateId){
			$helper->add(".template/" . $templateId, $templateDir . $templateId);
			$count++;
		}
		$config["Skeleton"]["information"]["template_count"] = $count;
		
		$dir = SOYCMS_Library::getLibraryDirectory();
		$count = 0;
		foreach($config["Library"] as $id){
			$helper->add(".library/" . $id, $dir . $id);
			$count++;
		}
		$config["Skeleton"]["information"]["library_count"] = $count;
		
		$dir = SOYCMS_Snippet::getSnippetDirectory();
		$count = 0;
		foreach(@$config["Snippet"] as $id){
			$helper->add(".snippet/" . $id, $dir . $id);
			$count++;
		}
		$config["Skeleton"]["information"]["snippet_count"] = $count;
		
		$dir = SOYCMS_Navigation::getNavigationDirectory();
		$count = 0;
		foreach(@$config["Navigation"] as $id){
			$helper->add(".navigation/" . $id, $dir . $id);
			$count++;
		}
		$config["Skeleton"]["information"]["navigation_count"] = $count;
		
		$dir = SOYCMS_SITE_DIRECTORY . "themes/";
		foreach(@$config["themes"] as $id){
			$helper->add("themes/" . $id, $dir . $id);
		}
		
		//.fields
		$dir = SOYCMS_SITE_DIRECTORY . ".field/";
		foreach(@$config["fields"] as $id){
			$helper->add(".field/" . $id, $dir . $id);
			if($id == "common.ini"){
				$files = soy2_scandir($dir);
				foreach($files as $file){
					if($file[0] == "_" || $file == "entry.ini"){
						$helper->add(".field/" . $file, $dir . $file);
					}
				}
			}
		}
		
		
		//圧縮実行
		$helper->setFilter(array($this,"convertURLFromFile"));
		$helper->compress();
		
		return $helper->getFileName();
    }
    
    /**
     * デザインのインポート
     */
    function importDesign($target,$config = null){
    	
		$srcDir = SOYCMS_ROOT_DIR . "tmp/" . $target . "/";
		
		if(!$config){
    		$config["Template"] = soy2_scandir($srcDir . ".template/");
    		$config["Library"] = soy2_scandir($srcDir . ".library/");
    		$config["Snippet"] = soy2_scandir($srcDir . ".snippet/");
    		$config["Navigation"] = soy2_scandir($srcDir . ".navigation/");
    		$config["themes"] = soy2_scandir($srcDir . "themes/");
    		$config["fields"] = soy2_scandir($srcDir . ".field/");
    	}
		
		$templateDir = SOYCMS_Template::getTemplateDirectory();
		foreach($config["Template"] as $templateId){
			$this->overwrite(
				$srcDir . ".template/" . $templateId,
				$templateDir . $templateId
			);
		}
		
		$dir = SOYCMS_Library::getLibraryDirectory();
		foreach($config["Library"] as $id){
			$this->overwrite(
				$srcDir . ".library/" . $id,
				$dir . $id
			);
		}
		
		$dir = SOYCMS_Snippet::getSnippetDirectory();
		foreach($config["Snippet"] as $id){
			$this->overwrite(
				$srcDir . ".snippet/" . $id,
				$dir . $id
			);
		}
		
		$dir = SOYCMS_Navigation::getNavigationDirectory();
		foreach($config["Navigation"] as $id){
			$this->overwrite(
				$srcDir . ".navigation/" . $id,
				$dir . $id
			);
		}
		
		$dir = SOYCMS_SITE_DIRECTORY . "themes/";
		foreach($config["themes"] as $id){
			$this->overwrite(
				$srcDir . "themes/" . $id,
				$dir . $id
			);
		}
		
		$dir = SOYCMS_SITE_DIRECTORY . ".field/";
		foreach($config["fields"] as $id){
			$this->overwrite(
				$srcDir . ".field/" . $id,
				$dir . $id
			);
			if($id == "common.ini"){
				$fields = soy2_scandir($srcDir . ".field/");
				
				if(file_exists($srcDir . ".field/entry.ini")){
					$this->overwrite(
						$srcDir . ".field/entry.ini",
						$dir . "entry/ini"
					);
				}
				
				foreach($fields as $file){
					if($file[0] == "_"){
						$this->overwrite(
							$srcDir . ".field/" . $file,
							$dir . $file
						);
					}
				}
				
			}
		}
		
    }
    
    /* util */
    private $host;
    private $path;
    private $url;
    
    /**
     * URL -> 置換文字列
     */
    function convertURL($content){
		
		if(!@$this->host){
			$this->url = soycms_get_site_url(true);
			$array = parse_url($this->url);
			$this->host = strtolower($array["scheme"]) . "://" . $array["host"] . "/";
			$this->path = $array["path"];
		}	
		$content = str_replace($this->url,"@@SITE_PATH@@",$content);
		$content = str_replace($this->host,"@@SITE_HOST@@",$content);
		
		if($this->path != "/"){
			$content = str_replace($this->path, "@@SITE_PATH@@",$content);
		}else{
			$content = str_replace('"' . $this->path, "\"@@SITE_PATH@@",$content);
		}
		
		return $content;
	}
	
	function convertURLFromFile($file){
		if(strpos($file,".html") === false)return;
		
		$content = file_get_contents($file);
		$content = $this->convertURL($content);
		file_put_contents($file,$content);
	}
	
	/**
	 * 置換文字列 -> URL
	 */
	function restoreURL($content){
		if(!@$this->host){
			$this->url = soycms_get_site_url(true);
			$array = parse_url($this->url);
			$this->host = strtolower($array["scheme"]) . "://" . $array["host"] . "/";
			$this->path = $array["path"];
		}
		
		$content = str_replace("@@SITE_PATH@@", $this->path ,$content);
		$content = str_replace("@@SITE_HOST@@",$this->host,$content);
		
		return $content;
	}
	
	function restoreURLFromFile($file){
		if(strpos($file,".html") === false)return;
		
		$content = file_get_contents($file);
		$content = $this->restoreURL($content);
		file_put_contents($file,$content);
	}
	
	/**
	 * ファイルの上書き処理
	 */
	function overwrite($src,$dst){
		
		if(file_exists($dst)){
			soy2_delete_dir($dst);
		}
		
		soy2_copy($src,$dst,array($this,"convertURL"));
		
	}
}
?>