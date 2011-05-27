<?php
SOY2HTMLFactory::importWebPage("page.page_page_detail");
/**
 * @title ディレクトリの設定 > エディタ設定
 */
class page_page_detail_editor extends page_page_detail{
	
	function doPost(){
		if(isset($_POST["save_editor_config"])){
			
			$config = $this->page->getConfigObject();
			
			if(isset($_POST["append_snippet_ids"])){
				$orders = array_keys($_POST["append_snippet_ids"]);
				$allows = $_POST["append_snippet_ids"];
				
				$config["append_snippet_order"] = $orders;
				$config["allowed_append_snippet"] = $allows;	
			}
			
			if(isset($_POST["InsertSnippetOrder"])){
				$ordersAppend = array_keys($_POST["InsertSnippetOrder"]);
				$allowsAppend = $_POST["insert_snippet_ids"];
				
				$config["insert_snippet_order"] = $ordersAppend;
				$config["allowed_insert_snippet"] = $allowsAppend;
			}
			
			$this->page->setConfigObject($config);
			$this->page->save();
			
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
		if(!in_array("nextpage",$orders)){
			$allowSnippet[] = "nextpage";
		}
		
		$this->createAdd("snippet_list","_class.list.SnippetList",array(
			"list" => $list,
			"selected" => $allowSnippet
		));
		
		$this->addCheckbox("next_page_snippet",array(
			"name" => "snippet_ids[]",
			"value" => "nextpage",
			"selected" => (empty($allowSnippet) || in_array("nextpage",$allowSnippet))
		));
		
		$this->addModel("is_directory",array(
			"visible" => ($this->page->isDirectory())
		));
	}
	
}