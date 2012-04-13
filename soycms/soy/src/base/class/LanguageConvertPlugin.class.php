<?php
/**
 * 管理画面の多言語化を行う
 */
class LanguageConvertPlugin extends HTMLPluginBase{
	function executePlugin($soyValue){
		$this->setInnerHTML('<?php echo soycms_convert_language("'.$soyValue.'"); ?>');
	}
}