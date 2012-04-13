<?php
/**
 * ページの作成、更新系
 */
class SOYCMS_PageLogic extends SOY2LogicBase{
	
	/**
	 * 作成
	 * @param SOYCMS_Page
	 * @param 引き継ぎ処理を行うかどうか(default true)
	 */
	function create(SOYCMS_Page $page, $flag = true){
		
		//URIのチェック
		$this->checkUri($page);
		$page->save();
		
		//Detailの時
		if(($page->getType() == "detail" || $page->getType() == "alias") && $page->getUri() != "_home"){
			$uri = $page->getUri();
			if($uri[strlen($uri)-1] != "/")$uri .= "/";
			
			//トップページを作成
			$index = new SOYCMS_Page();
			$index->setType("list");
			$index->setName($page->getName());
			$index->setUri(soycms_union_uri($uri,"index.html"));
			
			
			//テンプレートを指定
			if(isset($_POST["page_template"]) && strlen($_POST["page_template"]) > 0){
				$template = SOYCMS_Template::load($_POST["page_template"]);
				if($template){
					$index->setType($template->getType());
					$index->setTemplate($template->getId());
				}
			}
			
			$index->save();
				
			//default value
			$obj = $index->getPageObject();
			if(method_exists($obj,"setDirectory")){
				if($page->isDirectory()){
					$obj->setDirectory($page->getId());
				}else if($page->getType() == "alias"){
					$obj->setDirectory($page->getPageObject()->getDirectory());
				}
				$obj->save();
			}
			
			$this->generatePageClass($index);
			
		}
		
		$this->update($page);
		
		//ディレクトリを再帰的に作成
		$mapping = SOYCMS_DataSets::get("site.url_mapping",array());
		$uri = $page->getUri();
		$uris = explode("/",$uri);
		$tmpUrl = array_shift($uris);
		foreach($uris as $uri){
			if(!isset($mapping[$tmpUrl])){
				$tmpDir = new SOYCMS_Page();
				$tmpDir->setType("detail");
				$tmpDir->setName($tmpUrl);
				$tmpDir->setUri($tmpUrl);
				$tmpDir->save();
			}
			$tmpUrl .= "/" . $uri;
		}
		
		$this->updatePageMapping();
		
		//対応記事を作成
		$type = $page->getType();
		if($page->getType() != "detail" && $type[0] != "."){
			$entry = SOY2DAO::find("SOYCMS_Entry",array("directory"=>$page->getId()));
			if(count($entry) != 1){
				foreach($entry as $obj){
					$obj->delete();
				}
				$entry = new SOYCMS_Entry();
				$entry->setDirectory($page->getId());
				$entry->setTitle($page->getName());
				$entry->setUri("");
				$entry->setPublish(1);
				$entry->setOrder(0);
				$entry->setStatus("open");
				$entry->save();
			}
		}
		
		/* 親からの設定引き継ぎ系 */
		if($flag){
		
			//ディレクトリの時は親ディレクトリを引き継ぐ
			//ページの時はindex.htmlとテンプレート種別が同じ
			try{
				$parentDirectory = SOY2DAO::find("SOYCMS_Page",array("uri" => $page->getParentDirectoryUri()));
				$indexPage = SOY2DAO::find("SOYCMS_Page",array("uri" => $parentDirectory->getIndexUri()));
				
				if($page->isDirectory()){
					$this->copyConfigure($parentDirectory,$page);
					$this->copyConfigure($indexPage,$index);
				}else{
					$this->copyConfigure($indexPage,$page);
				}
				
			}catch(Exception $e){
				
			}
		
		}
		
	}
	
	/**
	 * 複製を行う
	 * @param src
	 * @param dst
	 */
	function duplicate(SOYCMS_Page $src,SOYCMS_Page $dst){
		
		$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
		
		//create
		$this->create($dst,false);
		
		//copy configure
		$this->copyConfigure($src,$dst);
		
		//copy class
		$this->copyClass($src,$dst);
		
		try{
			if($dst->getType() == "detail"){
				
				//index
				$index = $pageDAO->getByUri($dst->getIndexUri());
				$oldIndex = $pageDAO->getByUri($src->getIndexUri());
				
				//sync configure
				$index->setTemplate($oldIndex->getTemplate());
				
				//copy configure
				$this->copyConfigure($oldIndex,$index);
				
				//copy class
				$indexObj = $this->copyClass($oldIndex,$index);
				if(method_exists($indexObj, "setDirectory")){
					$indexObj->setDirectory($dst->getId());
					$indexObj->save();
				}
				
				
				$oldIndex->setId($index->getId());
				$oldIndex->setUri($index->getUri());
				$oldIndex->setName($index->getName());
				
				$oldIndex->save();
			}
		
		}catch(Exception $e){
			
		}
	}

	/**
	 * 更新
	 * 	URLのマッピング保存とか色々
	 */
	function update(SOYCMS_Page $page){
		$this->generatePageClass($page);
		$this->updatePageObject($page);
		
		
		//テンプレートの保存
		if(isset($_POST["template"])){
			$path = $page->getTemplateFilePath();
			if(!file_exists(dirname($path))){
				mkdir(dirname($path),umask(),true);
			}
			
			file_put_contents($path,$_POST["template"]);
		}
		
		
		$page->save();
		
		//URLが変更された場合
		//子ページのURLも変更する
		$map =  SOYCMS_DataSets::get("site.page_mapping",array());
		if(isset($map[$page->getId()]) && $map[$page->getId()]["uri"] != $page->getUri()){
			$tree = SOYCMS_DataSets::get("site.page_relation",array());
			if(isset($tree[$page->getId()])){
				$tree = $tree[$page->getId()];
				$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
				
				$old = "/^" . str_replace("/","\\/",$map[$page->getId()]["uri"]) . "/";
				$new = $page->getUri();
				
				foreach($tree as $childPageId){
					$_page = $dao->getById($childPageId);
					$uri = $_page->getUri();
					$_page->getPageObject();	//上書き時に保存されるように予め取得しておく
					
					//上書き変更
					$uri = preg_replace($old,$new,$uri);;
					$_page->setUri($uri);
					$_page->save();
					
					$this->generatePageClass($_page);
					$this->updatePageObject($_page);
				}
			}
		}
		
		//記事と同期をとります。
		if(($type = $page->getType()) != "detail" && $type[0] != "."){
			$entry = SOY2DAO::find("SOYCMS_Entry",array("directory"=>$page->getId()));
			$config = $page->getConfigObject();
			$publish = ($config["public"] == 1) ? 1 : 0;
			
			if(count($entry) != 1){
				foreach($entry as $obj){
					$obj->delete();
				}
				$entry = new SOYCMS_Entry();
				$entry->setDirectory($page->getId());
				$entry->setTitle($page->getName());
				$entry->setUri("");
				$entry->setPublish($publish);
				$entry->setOrder(0);
				if($publish){
					$entry->setStatus("open");
				}else{
					$entry->setStatus("close");
				}
				
				$entry->save();
			}else{
				$entry = array_shift($entry);
				$entry->setPublish($publish);
				$entry->setOrder(0);
				
				if($publish){
					$entry->setStatus("open");
				}else{
					$entry->setStatus("close");
				}
				
				$entry->save();
			}
		}
		
		//save object
		$obj = $page->getPageObject();
		$obj->save();
		
		
		//update mapping
		$this->updatePageMapping();
	}
	
	/**
	 * URLをチェックする
	 */
	function checkUri($page){
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		
		try{
			$_page = $dao->getByUri($page->getUri());
			if($_page->getId() == $page->getId()){
				return true;
			}
			
			$page->setUri($dao->getUniqueUri());
			
		}catch(Exception $e){
			return true;
		}
	}
	
	/**
	 * 削除(ゴミ箱に入れる)
	 */
	function delete($id){
		$page = SOY2DAOFactory::create("SOYCMS_PageDAO")->getById($id);
		$page->setDeleted($id);
		$page->save();
	}
	
	/**
	 * ページの実装クラスを生成
	 */
	function generatePageClass($obj,$force = false){
		
		/* プログラムファイル出力 */
		$classFilePath = $obj->getPageDirectory() . "class.php";
		
		if(file_exists($classFilePath)){
			SOY2::imports("site.public.base.class.*");
			SOY2::imports("site.public.base.page.*");
			include_once($classFilePath);
			if(!class_exists($obj->getCustomClassName())){
				$force = true;
			}else if(filemtime($classFilePath) < $obj->getCreateDate()){
				$force = true;
			}
		}
		
		if(!file_exists($classFilePath) || $force){
			$code = file_get_contents(dirname(__FILE__) . "/default/default.php");

			//replace
			$code = str_replace("%class%",$obj->getCustomClassName(),$code);
			$code = str_replace("%baseclass%",$obj->getBaseClassName(),$code);

			$header = "<?php //generated by soycms " . date("Y-m-d H:i:s") . "\n\n";
			$footer = "\n\n?>";
			file_put_contents($classFilePath,$header . $code . $footer);
		}
	}

	/**
	 * objectの保存
	 */
	function updatePageObject($page){
		$obj = $page->getPageObject();
		$obj->save();
		
		//対応記事がなかったら作成
		$type = $page->getType();
		if($page->getType() != "detail" && $type[0] != "."){
			$entry = SOY2DAO::find("SOYCMS_Entry",array("directory"=>$page->getId()));
			if(count($entry)<1){
				$entry = new SOYCMS_Entry();
				$entry->setDirectory($page->getId());
				$entry->setTitle($page->getName());
				$entry->setUri("");
				$entry->save();
			}
		}
	}
	
	/**
	 * マッピングを更新する
	 */
	function updatePageMapping(){
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();

		$mapping = array();
		$uriMapping = array();
		foreach($pages as $page){
			$id = $page->getId();
			$config = $page->getConfigObject();
			$mapping[$page->getId()] = array(
				"id" => $id,
				"type" => $page->getType(),
				"uri" => $page->getUri(),
				"name" => $page->getName(),
				"order" => $config["order"]
			);
			
			$uriMapping[$page->getUri()] = $page->getId();
		}
		
		//mappingを並べる
		uasort($mapping,create_function('$a,$b',
		'if(dirname($a["uri"]) == dirname($b["uri"])){
			if($a["order"] == $b["order"])return 0;
			return ($a["order"] > $b["order"]) ? 1 : -1;
		}else{
			return dirname($a["uri"])>dirname($b["uri"]);
		}'));
		
		//IDとURIのマッピングを保存
		SOYCMS_DataSets::put("site.page_mapping",$mapping);
		SOYCMS_DataSets::put("site.url_mapping",$uriMapping);
		
		//treeを作成
		asort($mapping);

		$tree = array();
		$relation = array();
		$root = (isset($uriMapping["_home"])) ? $uriMapping["_home"] : null;
		
		foreach($uriMapping as $uri => $id){
			if(!isset($tree[$id]))$tree[$id] = array();
			foreach($uriMapping as $_uri => $_id){
				if($_uri == $uri)continue;
				if($uri == "_home"){
					$tree[$id][] = $_id;
				}
				if(strpos($_uri,$uri) === 0 && $_uri[strlen($uri)] == "/"){
					$tree[$id][] = $_id;
				}
			}
		}
		
		//親 -> array(子,子,子,子,子)
		SOYCMS_DataSets::put("site.page_relation",$tree);
				
		//子が少ない順にソート
		uasort($tree,create_function('$a,$b','return (count($a) >= count($b));'));
		foreach($tree as $id => $array){
			$treeArray = array();
			foreach($array as $key => $pageId){
				if(isset($tree[$pageId])){
					$treeArray[$pageId] = $tree[$pageId];
					unset($tree[$pageId]);
					unset($relation[$pageId]);
				}
			}
			$tree[$id] = $treeArray;
		}
		
		//treeの保存
		SOYCMS_DataSets::put("site.page_tree",$tree);
		
		//pathを作成
		// ID => 親ID...自分というマッピング
		$path = array();
		
		foreach($pages as $page){
			$id = $page->getId();
			$uri = $page->getUri();
			$parentIds = array();
			
			foreach($uriMapping as $pageUri => $pageId){
				if($pageId == $id)continue;
				if($pageUri == "_home"){
					array_unshift($parentIds,(int)$pageId);
					continue;
				}
				
				if(strpos($uri,$pageUri) === 0 && strlen($uri) > strlen($pageUri) && $uri[strlen($pageUri)] == "/"){
					$parentIds[] = (int)$pageId;
					continue;
				}
			}
			$parentIds[] = (int)$id;
			$parentIds = array_unique($parentIds);
			$path[$id] = $parentIds;
		}
		
		
		SOYCMS_DataSets::put("site.page_tree_path",$path);
		
		
		
	}
	
	/**
	 * srcからdstに設定を引き継ぐ
	 * @param src
	 * @param dst
	 */
	function copyConfigure($src,$dst){
		//テンプレートが異なる場合はコピーしない
		if($dst->getTemplate() != $src->getTemplate()){
			return;
		}
		
		//要素設定の引き継ぎ
		$dst->saveItemConfig($src->loadItemConfig());
		
		//ブロックのコピー
		$srcDir = $src->getPageDirectory() . "block/";
		$dstDir = $dst->getPageDirectory() . "block/";
		if(!file_exists($dstDir))soy2_mkdir($dstDir);
		$blocks = (file_exists($srcDir)) ? soy2_scandir($srcDir) : array();
		foreach($blocks as $block){
			copy($srcDir . $block, $dstDir . $block);
		}
		
		//プロパティのコピ
		$dst->setProperties($src->getProperties());
		
		//カスタムフィールドのコピー
		$srcConfig = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $src->getId());
		SOYCMS_ObjectCustomFieldConfig::saveConfig("entry-" . $dst->getId(), $srcConfig);
		
	}
	
	/**
	 * オブジェクトの設定をコピー
	 */
	function copyClass($src,$dst){
		//種別が違う場合はコピーをしない
		if($dst->getType() != $src->getType()){
			return;
		}
		
		$srcObj = $src->getPageObject();
		$dstObj = $dst->getPageObject();
		
		SOY2::cast($dstObj,$srcObj);
		
		$dstObj->save();
		return $dstObj;
	}
}
?>