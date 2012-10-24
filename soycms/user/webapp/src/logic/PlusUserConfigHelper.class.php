<?php

class PlusUserConfigHelper extends SOY2LogicBase{

	/**
	 * アプリケーションIDの設定などを行います
	 */
	function syncConfig(){
		$config = PlusUserConfig::getConfig();
		$pageId = $config->getMemberPageId();
		$pageUrl = $config->getMemberPageUrl();
		$pageDAO = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$page = null;
		try{
			if($pageId){
				$page = $pageDAO->getById($pageId);
			}else{
				$page = $pageDAO->getByUri($pageUrl);
			}
			
			if($page->getType() != "app"){
				throw new Exception();
			}
			
		}catch(Exception $e){
			$page = new SOYCMS_Page();
			$page->setName("マイページ");
			$page->setUri($pageUrl);
			$page->setType("app");
			
			//テンプレートを適当に取得
			$page->setTemplate($this->getTemplateId());
			$page->save();
		}
		
		$pageConfig = $page->getPageObject();
		$pageConfig->setApplicationId("plus_user_connector");
		$pageConfig->save();
		
		
		$config->setMemberPageId($page->getId());
		PlusUserConfig::saveConfig($config);
		
		//
		SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic")->updatePageMapping();
		
	}
	
	/**
	 * テンプレートを適当に保存
	 */
	function getTemplateId(){
		return "_user/app";
	}
	
	/**
	 * 標準テンプレートを保存
	 */
	function saveDefaultTemplates(){
		$templateDir = PLUSUSER_ROOT_DIR . "template/";
		soy2_copy($templateDir, SOYCMS_SITE_DIRECTORY . ".template/_user/",array($this,"convertValues"));
	}

	function convertValues($filepath){
		if(preg_match('/\.html$/',$filepath)){
			$path = soycms_get_site_path();
			$content = file_get_contents($filepath);
			$content = str_replace("@@SITE_PATH@@",$path,$content);
			file_put_contents($filepath,$content);
		}
	}

}
?>