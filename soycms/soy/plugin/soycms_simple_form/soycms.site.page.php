<?php
class SOYCMS_SimpleFormFieldConfigPage extends SOYCMS_SitePageExtension{
	
	function SOYCMS_SimpleFormFieldConfigPage(){
		SOY2::imports("*", dirname(__FILE__) . "/src/");
		include(dirname(__FILE__) . "/src/pages/SOYCMS_SimpleContactFormExtension_FormConfigPage.class.php");
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "コンタクトフォームの設定";
	}
	
	function doPost(){
		
		//要素の追加
		//要素の設定の更新
		//要素の削除
		if(isset($_POST["NewField"])){
			
		}
		
		$this->getPageObject()->doPost();
		
		
	}
	
	function getPageObject(){
		return SOY2HTMLFactory::createInstance("SOYCMS_SimpleContactFormExtension_FormConfigPage");
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		$webPage = $this->getPageObject();
		return $webPage->getObject();
	}
}
PluginManager::extension("soycms.site.page","soycms_simple_form","SOYCMS_SimpleFormFieldConfigPage");
