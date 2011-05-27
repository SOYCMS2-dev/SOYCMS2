<?php
/**
 * cms:include="XXXX"を処理
 * モジュールを読み込んで表示する
 */
class SOYCMS_IncludeModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";
		
	function execute(){
		$soyValue = $this->soyValue;
		
		$this->setInnerHTML(
						'<!-- soy:id="library_'.$soyValue.'_wrap" -->' . 
							'<?php SOYCMS_ItemWrapComponent::startTag("library","'.$soyValue.'"); ?>' .
								'<?php SOYCMS_IncludeModulePlugin::loadModule("'.$soyValue.'"); ?>' .
							'<?php SOYCMS_ItemWrapComponent::endTag("library","'.$soyValue.'"); ?>' .  
						'<!-- /soy:id="library_'.$soyValue.'_wrap" -->' );	
	}
	
	/**
	 * モジュールを読み込む
	 * @param modulename
	 */
	public static function loadModule($moduleName){
		
		$dir = SOYCMS_SITE_DIRECTORY . ".library/" . $moduleName;
		if(!file_exists($dir)){
			echo "[ERROR]" . $moduleName . " is not found.\n";
			return;
		}
		
		include($dir . "/template.html");
		
	}
}
?>