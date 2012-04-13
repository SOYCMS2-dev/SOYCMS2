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
			$tree = array_reverse($tree);
			$tree = array_shift($tree);
			$parent = array_shift($tree);
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
	
	public static function getTaskHTML(){
		$root_url = SOYCMS_ROOT_URL;
		$task_url = SOYCMS_ROOT_URL . "site/task?layer&page=" . SOYCMS_Helper::get("page_id") . "&entry=" . SOYCMS_Helper::get("entry_id");
		
		return <<<HTML
<div id="dynamic-proofreading"><p class="dynamic-add-link-wrap"><span class="dynamic-add-link">？</span></p></div>
<style type="text/css">
@import "{$root_url}common/css/dynamic-task.css";
</style>
<script type="text/javascript">
var SOYCMS_TASK_URL = "{$task_url}";
</script>
<script type="text/javascript" src="{$root_url}common/js/dynamic/task.js"></script>
HTML;
	}
	

}
?>