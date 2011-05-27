<?php

class PageForm extends HTMLForm{
	
	private $page;
	private $prefix;
	private $parentName;

	function execute(){
		
		$page = $this->getPage();
		$this->buildForm($page);
		
		parent::execute();
		
	}
	
	function buildForm($page){
		
		//種別
		$types = array(
			"list" => "記事一覧",
			"default" => "単一ページ",
			/* "detail" => "記事ディレクトリ",
			"blog" => "ブログ", */
			"search" => "検索",
			"app" => "アプリケーション"
		);	
		
		$this->addInput("page_type_dir",array(
			"name" => "Page[type]",
			"value" => "detail"
		));
		
		$this->addInput("page_uri_prefix",array(
			"name" => "uri_prefix",
			"value" => $this->getPrefix()
		));
		
		$this->addLabel("page_uri_prefix_text",array(
			"text" => $this->getPrefix()
		));
		
		$this->addLabel("site_url",array(
			"text" => SOYCMS_SITE_ROOT_URL
		));
		
		$counter=1;
		foreach($types as $key => $value){
			$this->addCheckbox("page_type_" . $counter,array(
				"name" => "Page[type]",
				"value" => $key,
				"label" => $value,
				"selected" => ($key == $page->getType()),
				"visible" => $page->getType() != "app"
			));
			$counter++;
		}
		
		$this->addCheckbox("page_type_app",array(
				"name" => "Page[type]",
				"value" => "app",
				"label" => "アプリケーション",
				"selected" => true,
				"visible" => $page->getType() == "app"
			));
		
		$this->addModel("page_type_wrap",array(
			"visible" => isset($types[$page->getType()])
		));
		
		$this->addLabel("page_type_text",array(
			"text" => (isset($_GET["type_text"])) ? $_GET["type_text"] : $page->getTypeText(),
			"visible" => !isset($types[$page->getType()])
		));
		
		$this->addInput("page_type",array(
			"name" => "Page[type]",
			"value" => $page->getType(),
		));
		
		//名前
		$this->addInput("page_name",array(
			"name" => "Page[name]",
			"value" => $page->getName()
		));
		
		//URI
		$this->addInput("page_uri",array(
			"name" => "Page[uri]",
			"value" => $page->getUri()
		));
		
		//親ページ名
		$this->addLabel("parent_page_name",array(
			"text" => $this->parentName
		));
		
		//objectの設定
		$this->addInput("object_config",array(
			"name" => "object_config",
			"value" => (isset($_GET["object"])) ? base64_encode(soy2_serialize($_GET["object"])) : "",
			"visible" => (isset($_GET["object"]))
		));
		
	}

	function getPage() {
		if(!$this->page){
			$this->page = new SOYCMS_Page();
			if(isset($_GET["page_type"]))$this->page->setType(@$_GET["page_type"]);
			if(@$_GET["type"] == "dir" || !isset($_GET["type"]))$this->page->setType("detail");
			$typeText = (isset($_GET["type_text"])) ? $_GET["type_text"] : $this->page->getTypeText();
			$this->page->setName("新しい" . $typeText);
			if($this->page->isDirectory()){
				$this->page->setUri("newdir");
			}else{
				$this->page->setUri("newpage.html");
			}
		}
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}

	function getPrefix() {
		try{
			if(!$this->prefix && isset($_GET["parent"])){
				
					$page = SOY2DAO::find("SOYCMS_Page",$_GET["parent"]);
					$prefix = $page->getUri();
					if($prefix != "_home"){
						if($prefix[strlen($prefix)-1] != "/")$prefix .= "/";
						$this->prefix = $prefix;
					}
					$this->parentName = $page->getName();
					
					if(isset($_GET["index"])){
						$this->page->setName("インデックス of " . $page->getName());
						$this->page->setUri("index.html");
					}
					
				
			}else{
				$page = SOY2DAO::find("SOYCMS_Page",array("uri" => "_home"));
				$this->parentName = $page->getName();
			}
		}catch(Exception $e){
				
		}
		
		return $this->prefix;
	}
	function setPrefix($prefix) {
		$this->prefix = $prefix;
	}
}
?>