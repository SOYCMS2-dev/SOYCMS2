<?php
/**
 * cms:navigation="XXXX"を処理
 * ナビゲーションを読み込んで表示する
 */
class SOYCMS_NavigationModulePlugin extends HTMLPluginBase{

	protected $_soy2_prefix = "cms";
		
	function execute(){
		$soyValue = $this->soyValue;
		
		$this->setInnerHTML(
				'<!-- soy:id="navigation_'.$soyValue.'_wrap" -->' .
				'<?php SOYCMS_ItemWrapComponent::startTag("navigation","'.$soyValue.'"); ?>' .
					'<?php SOYCMS_NavigationModulePlugin::loadNavigation("'.$soyValue.'","'.SOY2HTMLConfig::Language().'"); ?>' .
				'<?php SOYCMS_ItemWrapComponent::endTag("navigation","'.$soyValue.'"); ?>' .
				'<!-- /soy:id="navigation_'.$soyValue.'_wrap" -->'
		);
	}
	
	public static function loadNavigation($moduleName,$language = null){
		
		$dir = SOYCMS_Navigation::getNavigationDirectory() . $moduleName . "/";
		if(!file_exists($dir)){
			echo "[ERROR]" . $moduleName . " is not found.\n";
			return;
		}
		
		try{
			$page = SOY2HTMLFactory::createInstance("SOYCMS_NavigationModulePage",array(
				"arguments" => array("navigationId" => $moduleName,"language" => $language),
			));
			
			$page->display();
		}catch(Exception $e){
			var_dump($e);
		}
	}
}

class SOYCMS_NavigationModulePage extends HTMLPage{
	
	private $language = null;
	private $navigationId;
	private $items = array();
	
	function SOYCMS_NavigationModulePage($args){
		$this->navigationId = $args["navigationId"];
		if(isset($args["language"]) && !empty($args["language"]))$this->language = $args["language"];
		$navigation = SOYCMS_Navigation::load($this->navigationId);
		$this->setId($this->navigationId);
		
		HTMLPage::HTMLPage();
		
		$items = $navigation->getItems();
		$this->items = $items;
		
		$this->parseInclude();
		$this->parseBlock();
	}
	
	function getTemplateFilePath(){
		$dir = SOYCMS_Navigation::getNavigationDirectory() . $this->getNavigationId() . "/";
		if($this->language && file_exists($dir . $this->language . ".html")){
			return $dir . $this->language . ".html";
		}
		return $dir . "template.html";
	}
	
	
	/**
	 * cms:includeの処理
	 */
	function parseInclude(){
		
		//リンクの置換え
		$plugin = new SOYCMS_IncludeModulePlugin();
		
		while(true){
			list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) =
				$plugin->parse("include","[a-zA-Z0-9\-_.]+",$this->_soy2_content);
			
			if(!strlen($tag))break;
			
			$plugin->_attribute = array();
			
			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
			
			//閉じ忘れinclude対策
			if(!$outerHTML){
				$this->_soy2_content = str_replace("<".$line.">","",$this->_soy2_content);
			}
		}
	}
	
	/**
	 * block:id="XXX"の処理
	 */
	function parseBlock(){
		
		$items = $this->getItems();
		
		$inst = SOYCMS_SiteController::getInstance();
		$page = $inst->getPageObject();
		$dir = $inst->getDirectoryObject();
		
		foreach($items as $item){
			if($item->getType() == "block"){
				$block = SOYCMS_Block::load($item->getId(),SOYCMS_Navigation::getNavigationDirectory() . $item->getNavigationId() . "/block/");
				$blockObj = $block->getObject();
				
				$this->createAdd($block->getId(), "SOYCMS_BlockComponent",array(
					"block" => $block,
					"soy2prefix" => "block",
					"parentPageParam" => $this->navigationId,
					"page" => $page,
					"directory" => $dir
				));
				
			}else{
				$this->addModel($item->getType() . "_" . $item->getId() . "_wrap",array(
					"visible" => true,
					"soy2prefix" => "soy"
				));
			}
			
		}
		
		
	}
	
	function getLayout(){
		return "blank";
	}
		

	function getNavigationId() {
		return $this->navigationId;
	}
	function setNavigationId($navigationId) {
		$this->navigationId = $navigationId;
	}

	function getItems() {
		return $this->items;
	}
	function setItems($items) {
		$this->items = $items;
	}
	
}
?>