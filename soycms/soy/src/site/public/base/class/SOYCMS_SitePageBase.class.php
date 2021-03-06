<?php
class SOYCMS_SitePageBase extends WebPage{

	private $entryTitle = "";
	
	/**
	 * @var SOYCMS_Page
	 */
	private $pageObject;
	private $directoryObject;
	private $_itemConfig;
	private $arguments = array();
	
	function SOYCMS_SitePageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);
		
		WebPage::WebPage();
	}
	
	function init(){
		PluginManager::load("soycms.site.public.*");
		PluginManager::load("soycms.site.entry.output.*");
		
		if(SOYCMS_MANAGE_MODE){
			$template = SOYCMS_Template::load($this->pageObject->getTemplate());
			if($template){
				$suffix = $template->getId();
				$type = $template->getTemplateType();
				if($template->getId() != $type){
					$suffix .= "&template=" . $type;
				}
				SOYCMS_DynamicEditHelper::template("template", $template->getName(), SOYCMS_ADMIN_ROOT_URL . "site/page/template/detail?id=" . $suffix);
			}
		}
	}
	
	/**
	 * クラス毎に異なるbuild
	 */
	function build($args){
		
	}
	
	/**
	 * 共通のbuild
	 */
	public final function common_build($args){
		$pageObj = $this->getPageObject();
		$config = $pageObj->getConfigObject();
		
		//invoke event
		PluginManager::invoke("soycms.site.public.common_build",array(
			"htmlObj" => $this,
			"pageObj" => $pageObj
		));
		
		//該当ページの公開設定確認
		$this->checkPagePublicConfig($config);
		
		//ディレクトリ以外は親の公開設定を引き継ぐ
		if(!$pageObj->isDirectory()){
			
			//parent
			$dirname = str_replace(".","",dirname($pageObj->getUri()));
			if(!$dirname)$dirname = "_home";
			$parent = SOY2DAO::find("SOYCMS_Page",array("uri" => $dirname));
			
			$config = $parent->getConfigObject();
			$this->checkPagePublicConfig($config);
			
		}
		
		//block:entryの処理
		$type = $pageObj->getType();
		if($type != "detail" && $type[0] != ".feed"){
			$entry = SOY2DAO::find("SOYCMS_Entry",(array("directory" => $pageObj->getId())));
			$entry = (count($entry)>0) ? array_shift($entry) : null;
			if(!$entry){
				$entry = new SOYCMS_Entry();
				$entry->setTitle($pageObj->getName());
				$entry->setDirectory($pageObj->getId());
				$entry->save();
			}
			$this->buildEntryBlock($entry);
		}
		
		//layoutの処理
		if($this->_soy2_content){
			$this->_soy2_content = preg_replace('/<!--\s*\/?layout:[\S]+\s*-->([\r\n])?/',"",$this->_soy2_content);
		}
		
		$this->build($args);
	
	}
	
	/**
	 * 公開設定を確認する
	 * 非公開時は表示しない
	 * Basic認証を発行する
	 */
	function checkPagePublicConfig($config){
		
		//プラグイン拡張
		if(!is_numeric($config["public"])){
			PluginManager::load("soycms.site.page.permisson");
			$delegate = PluginManager::invoke("soycms.site.page.permisson",array(
				"mode" => "page",
				"pageId" => $this->getPageObject()->getId(),
				"moduleId" => $config["public"]
			));
			
			//error
			if(!$delegate->getResult()){
				throw new Exception("closed");
			}else{
				return true;
			}
		}
		
		//非公開の時
		if(@$config["public"] < 1){
			
			//プレビュー時は表示出来る
			if(@SOYCMS_ADMIN_LOGINED && isset($_GET["preview"]) && !defined("SOYCMS_EDIT_DYNAMIC")){
				//ok
			}else{
				throw new Exception("closed");
			}
		}
		
		//サイト全体がBasic
		$modeBasicAuth = SOYCMS_DataSets::get("mode_basic_auth",0);
		
		if($modeBasicAuth){
			soycms_basic_auth(SOYCMS_DataSets::get("mode_basic.id",""),SOYCMS_DataSets::get("mode_basic.pass",""));
		}
		
		
		//basic
		if($config["public"] == 2 && !$modeBasicAuth){
			soycms_basic_auth($config["public_option_id"],$config["public_option_pass"]);
		}
	}

	/**
	 * このメソッドは拡張して実行されます
	 */
	function main($args){

	}

	/**
	 * 共通処理
	 */
	public final function common_execute(){
		$pageObj = $this->getPageObject();
		$config = $pageObj->getConfigObject();
		$dirConfig = $this->directoryObject->getConfigObject();
		
		//invoke event
		PluginManager::invoke("soycms.site.public.common_execute",array(
			"htmlObj" => $this,
			"pageObj" => $pageObj
		));
		
		//parse cms:include
		$this->parseInclude();
		
		//parse cms:navigation
		$this->parseNavigation();
		
		//parse block
		$this->parseBlock();
		
		//parse directory category list
		$this->buildDirectoryLabeList();
		
		//共通ページ名
		$this->addLabel("page_name",array("text" => $pageObj->getName(),"soy2prefix" => "cms"));
		$this->addLabel("site_name",array("text" => SOYCMS_DataSets::load("site_name",SOYCMS_SITE_ID),"soy2prefix" => "cms"));
		$this->addLink("site_link",array("link" => soycms_get_site_url(), "soy2prefix" => "cms"));
		$this->addLink("site_url",array("text" => soycms_get_site_url(), "soy2prefix" => "cms"));
		
		$current_url = $_SERVER["REQUEST_URI"];
		if(strpos($current_url,"?") !== false)$current_url = substr($current_url, 0, strpos($current_url,"?"));
		if(strpos($current_url,"/index.html") !== false)$current_url = substr($current_url, 0, strpos($current_url,"/index.html"));
		if(strpos($current_url, ".") === false && strlen($current_url) > 1 && $current_url[strlen($current_url)-1] != "/")$current_url .= "/";
		
		$this->addLink("page_link",array("link" => $current_url,"soy2prefix" => "cms"));
		$this->addLabel("page_url",array("text" => soycms_get_page_url($pageObj->getUri()),"soy2prefix" => "cms"));
		
		//meta
		$this->buildPageInfo($config,$dirConfig);
		
		$title = (strlen(@$config["title"]) > 0) ? @$config["title"] : $pageObj->getName();
		$title = $this->convertTitle($title);
		$this->setTitle($title);
		
		//for fix cache
		$this->getBodyElement();
		$this->getHeadElement();
		
		//dynamic edit link
		//ダイナミック編集中ではない時かつログインしている時
		$this->createAdd("dynamic_edit_navi","SOYCMS_DynamicEditNaviComponent",array(
			"visible" => @SOYCMS_ADMIN_LOGINED && !@SOYCMS_EDIT_DYNAMIC,
			"soy2prefix" => "cms"
		));
		
		//ダイナミック編集
		if(defined("SOYCMS_EDIT_DYNAMIC") && SOYCMS_EDIT_DYNAMIC == true){
			SOY2::import("site.public.dynamic.SOYCMS_DynamicEditHelper");
			SOYCMS_DynamicEditHelper::prepare($this);
		}
		
		//記事ブロックのカスタムフィールドの処理
		//SOYCMS_EntryListComponentからのコピー
		//記事のカスタムフィールドを全体でも利用するため
		$commonConfig = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
		$values = SOYCMS_ObjectCustomField::getValues("entry",SOYCMS_Helper::get("entry_id"));
		
		foreach($commonConfig as $key => $fieldConfig){
			if(isset($values[$key])){
				$value = $values[$key];
			}else{
				$value = $fieldConfig->getValueObject();
				if($fieldConfig->isMulti()){
					$value = array($value);
				}
			}
			SOYCMS_ObjectCustomFieldHelper::build($this,$fieldConfig,$value);
		}
		
		//
		if(@SOYCMS_ADMIN_LOGINED){
			//SOY2::import("site.public.dynamic.SOYCMS_DynamicEditHelper");
			//if(strpos($config["content-type"],"html") !== false){
			//	$this->getBodyElement()->appendHTML(SOYCMS_DynamicEditHelper::getTaskHTML());
			//}
		}
	}
	
	/**
	 * block:entryの処理
	 */
	function buildEntryBlock($entry){
		
		SOYCMS_Helper::set("entry_id",$entry->getId());
		SOYCMS_Helper::set("entry_uri",$entry->getUri());
		SOYCMS_Helper::set("entry_directory",$entry->getDirectory());
		SOYCMS_Helper::set("entry_title", $entry->getTitle());
		
		$visible = $this->isItemVisible("default:entry");
		$url= "";
		
		if(isset($_GET["preview"])){
			$session = SOY2Session::get("site.session.SiteLoginSession");
			if($session->getSiteId() != SOYCMS_SITE_ID){
				throw new Exception();
			}else{
				if(strlen($_GET["preview"])>0){
					try{
						SOY2::import("site.domain.history.SOYCMS_EntryHistory");
						define("SOYCMS_LOGIN_SITE_ID",SOYCMS_SITE_ID);
						$hisotry = SOY2DAO::find("SOYCMS_EntryHistory",$_GET["preview"]);
						if($hisotry->getEntryId() == $entry->getId()){
							$hisotry->merge($entry);
						}
					}catch(Exception $e){
						
					}
				}
				
				//プレビューの時は最新の値を表示する
				$entry->setContent($entry->buildContent());
				$entry->setTitle($entry->getTitleSection());
				
			}
		}else{
			
			//invoke event
			PluginManager::invoke("soycms.site.entry.output.detail",array(
				"entry" => $entry,
				"mode" => "detail"
			));
			
		}
		
		$this->createAdd("entry","SOYCMS_EntryDetailComponent",array(
			"list" => array($entry),
			"mode" => "detail",
			"soy2prefix" => "block",
			"visible" => $visible,
			"directory" => $entry->getDirectory(),
			"directoryUri" => ($this->getPageObject()->getType() == "detail") ? $this->getPageObject()->getUri() : $this->getPageObject()->getParentDirectoryUri(),
			"link" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/entry/detail/" : ""
		));
		
		//entry_list_wrap
		$visible = $this->isItemVisible("default:entry_list");
		$this->addModel("entry_list_wrap",array(
			"visible" => $visible,
			"soy2prefix" => "cms"
		));
	}
	
	/**
	 * cms:includeの処理
	 */
	function parseInclude(){
		
		//リンクの置換え
		$plugin = new SOYCMS_IncludeModulePlugin();
		$this->executePlugin("include",$plugin);
	}
	
	/**
	 * cms:navigationの処理
	 */
	function parseNavigation(){
		
		//リンクの置換え
		$plugin = new SOYCMS_NavigationModulePlugin();
		$this->executePlugin("navigation",$plugin);
		
	}
	
	/**
	 * プロパティの置換
	 * @override
	 */
	function parseMessageProperty(){
		$propertyFile = SOYCMS_Template::getTemplateDirectory() . $this->pageObject->getTemplate() . "/properties.ini";
		
		$properties = (file_exists($propertyFile)) ? @parse_ini_file($propertyFile) : array();
		$_properties = $this->pageObject->getProperties();
		foreach($properties as $key => $value){
			if(isset($_properties[$key])){
				$properties[$key] = $_properties[$key];
			}
			
			$this->_soy2_content =
				str_replace("##" . $key . "##", '<?php echo $page["_properties"]["'.$key.'"]; ?>' ,$this->_soy2_content);
		}
		
		$this->_soy2_page["_properties"] = $properties;
		
	}
	
	/**
	 * block:id="XXX"の処理
	 */
	function parseBlock(){
		
		//テンプレートのブロックを取得
		$pageObj = $this->getPageObject();
		$items = $pageObj->getItems();
		$config = $pageObj->loadItemConfig();
		$dir = $this->getDirectoryObject();
		
		foreach($items as $key => $item){
			$visible = (isset($config[$key]) && $config[$key]["hidden"] > 0) ? false : true;
			
			if($item->getType() == "block"){
				
				//ページからブロックを取得
				$block = SOYCMS_PageItem::getBlock($pageObj,$item->getId(),true);
				
				
				
				if($block){
					$this->createAdd($block->getId(), "SOYCMS_BlockComponent",array(
						"block" => $block,
						"soy2prefix" => "block",
						"visible" => $visible,
						"page" => $pageObj,
						"directory" => $dir
					));
				}
			}else{
				$this->addModel($item->getType() . "_" . $item->getId() . "_wrap",array(
					"visible" => $visible,
					"soy2prefix" => "soy"
				));
			}
		}
	}
	
	/**
	 * Metaとか
	 */
	function buildPageInfo($config,$dirConfig){
		
		$this->addMeta("meta_content_type",array(
			"attr:http-equiv" => "Content-Type",
			"attr:content" => $config["content-type"] . "; charset=" . $config["encoding"],
			"soy2prefix" => "cms"
		));
		
		$language = SOYCMS_DataSets::get("site_language","ja");
		
		$this->addMeta("meta_content_language",array(
			"attr:http-equiv" => "Content-Language",
			"attr:content" => $language,
			"soy2prefix" => "cms"
		));
		
		$this->addMeta("meta_author",array(
			"attr:name" => "author",
			"attr:content" => SOYCMS_DataSets::get("site_autor",""),
			"soy2prefix" => "cms"
		));
		
		$this->addMeta("meta_copyright",array(
			"attr:name" => "copyright",
			"attr:content" => SOYCMS_DataSets::get("site_copyright",""),
			"soy2prefix" => "cms"
		));
		
		$this->addMeta("meta_keyword",array(
			"attr:name" => "keywords",
			"attr:content" => (@$config["keyword"]) ? @$config["keyword"] : SOYCMS_DataSets::get("site_keyword",""),
			"soy2prefix" => "cms",
			//"visible" => (strlen((@$config["keyword"])))
		));
		
		$this->addMeta("meta_description",array(
			"attr:name" => "description",
			"attr:content" => (@$config["description"]) ? @$config["description"] : SOYCMS_DataSets::get("site_description",""),
			"soy2prefix" => "cms",
			//"visible" => (strlen((@$config["description"])))
		));
		$this->addLabel("page_description",array(
			"html" => (@$config["description"]) ? nl2br(htmlspecialchars($config["description"])) : "",
			"soy2prefix" => "cms",
			//"visible" => (strlen(@$config["description"]) > 0)
		));
		$this->addLabel("dir_description",array(
			"html" => (@$dirConfig["description"]) ? nl2br(htmlspecialchars($dirConfig["description"])) : "",
			"soy2prefix" => "cms",
			//"visible" => (strlen(@$dirConfig["description"]) > 0)
		));
		
		//標準のfaviconを使う
		if(strlen($config["favicon"])<1 && file_exists(SOYCMS_SITE_DIRECTORY . "themes/icons/favicon.ico")){
			$config["favicon"] = "themes/icons/favicon.ico";
		}
		
		$this->addModel("link_shortcut_icon",array(
			"attr:rel" => "shortcut icon",
			"attr:href" => soycms_union_uri(soycms_get_site_path(),@$config["favicon"]),
			"visible" => (strlen($config["favicon"]) > 0),
			"soy2prefix" => "cms"
		));
		
		$this->addModel("link_icon",array(
			"attr:rel" => "icon",
			"attr:href" => soycms_union_uri(soycms_get_site_path(),@$config["favicon"]),
			"visible" => (strlen($config["favicon"]) > 0),
			"soy2prefix" => "cms"
		));
		
		$this->addImage("page_image",array(
			"src" => @$config["image"],
			"visible" => (strlen($config["image"]) > 0),
			"soy2prefix" => "cms"
		));
		$this->addImage("dir_image",array(
			"src" => @$dirConfig["image"],
			"visible" => (strlen($dirConfig["image"]) > 0),
			"soy2prefix" => "cms"
		));
		
		
		$this->addModel("link_start",array(
			"attr:rel" => "start",
			"attr:href" => soycms_get_page_url(""),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("site_language",array(
			"text" => $language,
			"soy2prefix" => "cms"
		));
		
		//feed周りのmeta
		$this->createAdd("site_feed","SOYCMS_FeedInfoLabel",array(
			"soy2prefix" => "cms"
		));
		$this->createAdd("directory_feed","SOYCMS_FeedInfoLabel",array(
			"page" => $this->getPageObject(),
			"soy2prefix" => "cms"
		));
	}

	/**
	 * 文字コード変換して出力
	 */
	function display(){
		
		$pageObj = $this->getPageObject();
		$config = null;
		if($pageObj)$config = $pageObj->getConfigObject();
		
		ob_start();
		parent::display();
		$html = ob_get_contents();
		ob_end_clean();

		
		
		if($config){
			if(!isset($config["encoding"]))$config["encoding"] = "UTF-8";
			header("Content-Type: " . $config["content-type"] . "; encoding=" .$config["encoding"] );
			echo mb_convert_encoding($html,$config["encoding"],"UTF-8");
		}else{
			echo $html;
		}
	}
	
	/**
	 * ディレクトリのカテゴリリスト
	 */
	function buildDirectoryLabeList(){
		
		$labels =
			($this->isItemVisible("default:directory_label_list",false))
				? SOY2DAO::find("SOYCMS_Label",array("directory" => $this->directoryObject->getId()))
				: array();
		
		$this->createAdd("directory_label_list","SOYCMS_LabelListWrapComponent",array(
			"dirUrl" => soycms_get_page_url($this->directoryObject->getUri()),
			"soy2prefix" => "block",
			"list" => $labels,
			"visible" => $this->isItemVisible("default:directory_label_list",false)
		));
	}
	
	/**
	 * 要素の表示、非表示をチェックする
	 * @return boolean
	 */
	function isItemVisible($id,$default = true){
		if(!$this->_itemConfig){
			$this->_itemConfig = $this->pageObject->loadItemConfig();
		}
		
		if(isset($this->_itemConfig[$id])){
			return ($this->_itemConfig[$id]["hidden"] > 0) ? false : true;
		}else{
			return $default;
		}
	}

	
	function getPageObject() {
		return $this->pageObject;
	}
	function setPageObject($pageObject) {
		$this->pageObject = $pageObject;
		
		if($pageObject->isDirectory()){
			$dir = $pageObject;
		}else{
			$mapping = SOYCMS_DataSets::load("site.page_mapping");
			$urls = SOYCMS_DataSets::load("site.url_mapping");
			$url = dirname($pageObject->getUri());
			if($url == ".")$url = "_home";
			try{
				if(!isset($urls[$url]))throw new Exception();	//プレビュー用
				$dir = SOY2DAO::find("SOYCMS_Page",$mapping[$urls[$url]]["id"]);
			}catch(Exception $e){
				$dir = new SOYCMS_Page();
			}
		}
		
		$this->setDirectoryObject($dir);
	}
	
	function getDirectoryObject() {
		return $this->directoryObject;
	}
	function setDirectoryObject($directoryObject) {
		$this->directoryObject = $directoryObject;
	}
	
	function getTemplateFilePath(){
		$obj = $this->getPageObject();
		return $obj->getTemplateFilePath();
	}
	/**
	 * @param isIncludeArguments
	 * @return string
	 */
	function getPageUrl($isIncludeArguments = false){
		$url = soycms_get_page_url($this->getPageObject()->getUri());
		if($isIncludeArguments){
			$url .= "/" . implode($this->getArguments(),"/");
		}
		return $url;
	}
	
	/**
	 * タイトルタグの置換
	 */
	function convertTitle($title){
		$pageObj = $this->getPageObject();
		
		//PageName
		$title = str_replace("#PageName#",$pageObj->getName(),$title);
		
		//SiteName
		$title = str_replace("#SiteName#",SOYCMS_DataSets::load("site_name",SOYCMS_SITE_ID),$title);
		
		//EntryTitle
		$title = str_replace("#EntryTitle#",$this->getEntryTitle(),$title);
		
		//DirName
		if($pageObj->isDirectory()){
			$title = str_replace("#DirName#",$pageObj->getName(),$title);
		}else{
			$mapping = SOYCMS_DataSets::load("site.page_mapping");
			$urls = SOYCMS_DataSets::load("site.url_mapping");
			$url = dirname($pageObj->getUri());
			if($url == ".")$url = "_home";
			$_title = (isset($urls[$url])) ? $mapping[$urls[$url]]["name"] : "";
			$title = str_replace("#DirName#",$_title,$title);
		}
		
		return $title;
	}

	function getArguments() {
		return $this->arguments;
	}
	function setArguments($arguments) {
		if(!is_array($arguments))$arguments = explode("/",$arguments);
		$this->arguments = $arguments;
	}
	
	function getLayout(){
		return "blank";
	}

	function getEntryTitle() {
		return $this->entryTitle;
	}
	function setEntryTitle($entryTitle) {
		$this->entryTitle = $entryTitle;
	}
	
	/**
	 * キャッシュ生成のタイミングを制御したい
	 */
	function isModified(){
		
		//ページの更新時刻 > テンプレートのHTMLの更新時刻
		if($this->getPageObject()->getUpdateDate() > @filemtime($this->getTemplateFilePath())){
			return true;
		}
		
		//ダイナミック編集中はキャッシュを更新し続ける
		if(defined("SOYCMS_EDIT_DYNAMIC") && SOYCMS_EDIT_DYNAMIC){
			return true;
		}
		
		return parent::isModified();
	}
}

class SOYCMS_PagerBase{
	
	function getCurrentPage(){}
	
	function getTotalPage(){}
	
	function getLimit(){}
	
	function getPagerUrl(){}
	
	function getNextPageUrl(){}
	
	function getPrevPageUrl(){}
	
	function hasNext(){ return false; }
	function hasPrev(){ return false; }
	
	function execute(){}
	
}