<?php
/**
 * cms:include="XXXX"を処理
 * モジュールを読み込んで表示する
 */
class SOYCMS_IncludeModulePlugin extends HTMLPluginBase{

	protected $_soy2_prefix = "cms";
	private $isWrap = true;
	
	function setWrapCode($flag){
		$this->isWrap = $flag;
	}
		
	function execute(){
		$soyValue = $this->soyValue;
		$language = SOY2HTMLConfig::Language();
		$template = $this->getAttribute("cms:template");
		if($template){
			$language = $template;
		}
		
		$this->setInnerHTML(
						(($this->isWrap) ? '<!-- soy:id="library_'.$soyValue.'_wrap" -->' : "") .
							'<?php SOYCMS_ItemWrapComponent::startTag("library","'.$soyValue.'"); ?>' .
								'<?php SOYCMS_IncludeModulePlugin::loadModule("'.$soyValue.'","'.$language.'"); ?>' .
							'<?php SOYCMS_ItemWrapComponent::endTag("library","'.$soyValue.'"); ?>' .
						(($this->isWrap) ? '<!-- /soy:id="library_'.$soyValue.'_wrap" -->' : "")
		);
	}
	
	/**
	 * モジュールを読み込む
	 * @param modulename
	 */
	public static function loadModule($moduleName,$language = null){
		
		$dir = SOYCMS_Library::getLibraryDirectory() . $moduleName;
		
		if(!file_exists($dir)){
			echo "[ERROR]" . $moduleName . " is not found.\n";
			return;
		}
		
		try{
			if($language && file_exists($dir . "/" . $language .".html")){
				include($dir . "/" . $language . ".html");
			}else{
				include($dir . "/template.html");
			}
		}catch(Exception $e){
			
		}
		
	}
}
?>