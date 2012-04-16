<?php

class SOYCMS_DynamicEditHelper {
	
	static private $_template = array();
	
	/**
	 * テンプレート情報を追加
	 * @param unknown_type $category
	 * @param unknown_type $label
	 * @param unknown_type $link
	 * @param unknown_type $option
	 */
	public static function template($category, $label, $link, $option = array()){
		
		if(!isset(self::$_template[$category])){
			self::$_template[$category] = array();
		}
		
		self::$_template[$category][$link] = array(
			"label" => $label,
			"link" => $link,
			"option" => $option
		);
	}
	
	
	public static function getManageMenuHTML(){
		$html = array();
		$result = self::$_template;
		
		$root = SOYCMS_ADMIN_ROOT_URL;
		$html[] = '<script type="text/javascript">var dynamic_manage_templates = '.json_encode($result).';</script>';
		$html[] = '<script type="text/javascript" src="'.$root.'common/js/dynamic/manage.js"></script>';
		$html[] = '<link rel="stylesheet" href="'.$root.'common/css/dynamic-manage.css" type="text/css" media="all" />';
		return implode("", $html);
	}
	
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