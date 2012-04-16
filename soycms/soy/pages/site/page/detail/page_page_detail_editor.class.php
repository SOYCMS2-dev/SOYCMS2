<?php
SOY2HTMLFactory::importWebPage("page.page_page_detail");
/**
 * @title ディレクトリの設定 > エディタ設定
 */
class page_page_detail_editor extends page_page_detail{
	
	function doPost(){
		//再帰的に設定するかどうか
		$isrecursive = (isset($_POST["save_editor_recursive"]) && $_POST["save_editor_recursive"] == 1);
		
		if(isset($_POST["save_editor_config"])){
			
			$pages = array($this->page->getId());
			
			//再帰的に
			if($isrecursive){
				$mappings = SOYCMS_DataSets::get("site.page_relation");
				$pages = array_merge($pages,$mappings[$this->page->getId()]);
			}
			
			$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
			foreach($pages as $pageId){
				try{
					$page = $pageDAO->getById($pageId);
				}catch(Exception $e){
					continue;
				}
				
				$this->saveEditorConfig($page);
			}
				
			$this->jump("/page/detail/editor/" . $this->id . "?updated");
			exit;
		}
	}
	
	function page_page_detail_editor($args){
		
		$this->id = @$args[0];
		$this->page = @$args[1];
		
		WebPage::WebPage();
			
		$this->buildTab();
		$this->buildPage();
		$this->bulidForm();
		
	}
	
	function buildPage(){
		
		parent::buildPage();
	}
	
	function bulidForm(){
		$this->addForm("form");
		
		$config = $this->page->getConfigObject();
		
		
		//挿入ボタン
		$orders = (isset($config["insert_snippet_order"])) ? $config["insert_snippet_order"] : array();
		$allowSnippet = (isset($config["allowed_insert_snippet"])) ? $config["allowed_insert_snippet"] : array();
		
		$list = SOYCMS_Snippet::getList($orders);
		
		$insertBtnList = array();
		
		foreach($list as $key => $snippet){
			if($snippet->getGroup()){
				unset($list[$key]);
				continue;
			}
			if($snippet->getType() == "wysiwyg"){
				$insertBtnList[$key] = $snippet;
				
				
				if(!in_array($key,$orders)){
					$allowSnippet[] = $key;
				}
				
				continue;
			}
		}
		
		$this->createAdd("snippet_popup_list","_class.list.SnippetList",array(
			"list" => $insertBtnList,
			"selected" => $allowSnippet,
			"mode" => "insert"
		));
		
		//追加ボタン
		$orders = (isset($config["append_snippet_order"])) ? $config["append_snippet_order"] : array();
		$allowSnippet = (isset($config["allowed_append_snippet"])) ? $config["allowed_append_snippet"] : array();
		$list = SOYCMS_Snippet::sortSnippet($list,$orders);
		foreach($list as $key => $snippet){
			if(!in_array($key,$orders)){
				$allowSnippet[] = $key;
			}
		}
		
		$this->createAdd("snippet_list","_class.list.SnippetList",array(
			"list" => $list,
			"selected" => $allowSnippet
		));
		
		$this->addCheckbox("next_page_snippet",array(
			"name" => "snippet_ids[]",
			"value" => "nextpage",
			"selected" => (in_array("nextpage",$allowSnippet))
		));
		
		$this->addModel("is_directory",array(
			"visible" => ($this->page->isDirectory())
		));
	}
	
	/**
	 * エディターの設定を保存
	 * @param SOYCMS_Page $page
	 */
	function saveEditorConfig(SOYCMS_Page $page){
		$config = $page->getConfigObject();
			
		if(isset($_POST["append_snippet_ids"])){
			$orders = array_keys($_POST["append_snippet_ids"]);
			$allows = $_POST["append_snippet_ids"];
			
			$config["append_snippet_order"] = $orders;
			$config["allowed_append_snippet"] = $allows;
			
		}else{
			$list = SOYCMS_Snippet::getList(array());
			$config["append_snippet_order"] = array_keys($list);
			$config["allowed_append_snippet"] = array();
		}
		
		if(isset($_POST["InsertSnippetOrder"])){
			$ordersAppend = array_keys($_POST["InsertSnippetOrder"]);
			$allowsAppend = (isset($_POST["insert_snippet_ids"])) ? $_POST["insert_snippet_ids"] : array();
			
			$config["insert_snippet_order"] = $ordersAppend;
			$config["allowed_insert_snippet"] = $allowsAppend;
		}
		
		$page->setConfigObject($config);
		$page->save();
	}
	
}