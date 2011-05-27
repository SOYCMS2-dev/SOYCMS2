<?php

class SOYCMS_DynamicEditHelper {
	
	public static function prepare($page){
		$page->getBodyElement()->insertHTML(self::getMenuHTML($page));
		$page->getBodyElement()->appendHTML(self::getPartHTML());
		
		$page->getHeadElement()->appendHTML(self::getHeadHTML());
	}
	
	public static function getHeadHTML(){
		$root = SOYCMS_ADMIN_ROOT_URL;
		$head = <<<HTML
<link rel="stylesheet" href="{$root}common/css/dynamic-edit.css" type="text/css" media="all" />

HTML;
		
		return $head;
	}
	
	public static function getMenuHTML($page){
		$pageObj = $page->getPageObject();
		$pageId = $pageObj->getId();
		$template = SOYCMS_Template::load($pageObj->getTemplate());
		$templateName = ($template) ? $template->getName() : $pageObj->getTemplate();
		
		$pageName = $pageObj->getName();
		
		$controll = SOYCMS_ADMIN_ROOT_URL . "site/page/detail/" . $pageId;
		$dir_controll = $controll;
		
		//ディレクトリかどうか
		$isDir = $pageObj->isDirectory();
		if(!$isDir){
			$tree = SOYCMS_DataSets::get("site.page_tree_path",array());
			$parent = array_shift(array_shift(array_reverse($tree)));
			$dir_controll = SOYCMS_ADMIN_ROOT_URL . "site/page/detail/" . $parent;
			$baseName = basename($pageObj->getUri());
		}
		
		$inner = "";
		
		 
		
		
		
		if(!$isDir){
			$inner .= "<li class=\"soycms_dynamic_edit_menu-page_edit\"><a href=\"{$controll}#tpl_config\"><em>{$pageName}({$baseName})のテンプレート編集</em></a></li>";
		}else{
			$inner .= "<li class=\"soycms_dynamic_edit_menu-directory_edit\"><a href=\"{$dir_controll}#tpl_config\"><em>{$pageName}ディレクトリのテンプレート編集</em></a></li>";
		}
		
		$inner .= "<li class=\"soycms_dynamic_edit_menu_preview\"><a href=\"?preview\"><em>プレビュー</em></a></li>";
		$inner .= "<li class=\"soycms_dynamic_edit_close\"><a href=\"?dynamic=off\"><em></em></a></li>";
		
		
		$html = <<<HTML
<div id="soycms_dynamic_edit_menu">
	<p style="">{$pageName} [{$templateName}]</p>
	<ul>
		$inner
	</ul> 
</div>

HTML;


		return $html;
	}
	
	
	public static function getPartHTML(){
		return <<<HTML

HTML;
	}
	

}
?>