<?php
/**
 * cms:navigation="XXXX"を処理
 * ナビゲーションを読み込んで表示する
 */
class SOYCMS_NavigationModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";
		
	function execute(){
		$soyValue = $this->soyValue;
		
		$this->setInnerHTML(
				'<!-- soy:id="navigation_'.$soyValue.'_wrap" -->' .
				'<?php SOYCMS_ItemWrapComponent::startTag("navigation","'.$soyValue.'"); ?>' . 
					'<?php SOYCMS_NavigationModulePlugin::loadNavigation("'.$soyValue.'"); ?>' .
				'<?php SOYCMS_ItemWrapComponent::endTag("navigation","'.$soyValue.'"); ?>' .
				'<!-- /soy:id="navigation_'.$soyValue.'_wrap" -->'			
		);
		
		
		//echo $this->_soy2_innerHTML;
		//exit;	
	}
	
	public static function loadNavigation($moduleName){
		
		$dir = SOYCMS_SITE_DIRECTORY . ".navigation/" . $moduleName . "/";
		if(!file_exists($dir)){
			echo "[ERROR]" . $moduleName . " is not found.\n";
			return;
		}
		
		$page = SOY2HTMLFactory::createInstance("SOYCMS_NavigationModulePage",array(
			"arguments" => array("navigationId" => $moduleName),
		));
		
		$page->display();
	}
}

class SOYCMS_NavigationModulePage extends HTMLPage{
	
	private $navigationId;
	private $items = array();
	
	function SOYCMS_NavigationModulePage($args){
		$this->navigationId = $args["navigationId"];
		$navigation = SOYCMS_Navigation::load($this->navigationId);
		$this->setId($this->navigationId);
		
		HTMLPage::HTMLPage();
		
		$items = $navigation->getItems();
		$this->items = $items;
		
		$this->parseInclude();
		$this->parseBlock();
	}
	
	function getTemplateFilePath(){
		return SOYCMS_Navigation::getNavigationDirectory() . $this->getNavigationId() . "/template.html";
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
					"visible" => true
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