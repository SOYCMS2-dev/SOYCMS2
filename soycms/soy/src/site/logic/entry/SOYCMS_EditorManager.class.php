<?php

class SOYCMS_EditorManager extends SOY2LogicBase{
	
	private $mode = "append";	//or insert
	private $list = null;
	
	private static function inst(){
		static $_inst;
		if(!$_inst)$_inst = new SOYCMS_EditorManager();
		return $_inst;
	}
	
	public static function release(){
		$inst = self::inst();
		$inst = null;
		unset($inst);
	}
	
	public static function buildSections($sections){
		
		if(empty($sections)){
			$section = new SOYCMS_EntrySection();
			$section->setType("wysiwyg");
			$section->setValue("<p> </p>");
			$section->setContent("<p> </p>");
			$sections = array(
				$section
			);
		}
		
		$inst = self::inst();
		$html = array();
		
		foreach($sections as $key => $section){
			$html[] = $inst->buildSection($key,$section);
		}
		return implode("",$html);
	}
	
	
	function buildSection($key,$section){
		$html = array();
		
		$html[] = 	'<div class="section_list">';
		$html[] = 		'<div class="article-header">';
		$html[] = 			'<div class="text-mode-panel">';
		$html[] = 				'<div class="panel">';
		$html[] = 					'<div class="panel-line">';
		$html[] = 						'<div class="panel-parts"><a href="javascript:void(0);" onclick="aobata_editor.find(this).swapMode();" class="icon-btn btn-html toggle-btn" title="HTML"><em>HTML</em></a></div>';
		$html[] = 						'<div class="panel-parts has_form">';
		$html[] = 							'<div id="downpanel_delformat" class="downpanel">';
		$html[] = 								'<a href="javascript:void(0);" onclick="return aobata_editor.showOption(this);" class="icon-btn btn-delformat interval" title="ソースフォーマット"><em>ソースフォーマット</em></a>';
		$html[] = 							'</div>';
		$html[] = 							'<div id="downpanel_delformat_option" class="downpanel-fix" style="display:none;">';
		$html[] = 								'<div class="downpanel-fix-inner">';
		$html[] = 									'<div class="mbreak">';
		$html[] = 										'<ul>';
		$html[] = 											'<!-- <li class="fl" style="width:50%;"><input type="checkbox" id="format_indent" checked /><label style="line-height:1;" for="format_indent">インデント</label></li> -->';
		$html[] = 											'<li class="fl" style="width:50%;"><input type="checkbox" id="format_span" checked /><label style="line-height:1;" for="format_span">無駄なspanの除去</label></li>';
		$html[] = 										'</ul>';
		$html[] = 									'</div>';
		$html[] = 									'<p class="ri" style="clear:both;">';
		$html[] = 										'<input class="s-btn" type="button" onclick="aobata_editor.find(this).format($(\'#downpanel_delformat_option input:checked\').map(function(){ return $(this).attr(\'id\'); }).get());aobata_editor.hideAllPopup();" value="OK" />';
		$html[] = 										'<input class="s-btn" type="button" onclick="aobata_editor.hideAllPopup();" value="Cancel" />';
		$html[] = 									'</p>';
		$html[] = 								'</div>';
		$html[] = 							'</div>';
		$html[] = 						'</div>';
		$html[] = 						'<div class="panel-parts">';
		$html[] = 							'<a class="icon-xme-btn btn-addelement" href="javascript:void(0);" title="セクションを追加" onclick="aobata_editor.show_insert_lines(this);"><em><strong>セクションを追加</strong></em></a>';
		$html[] = 						'</div>';
		$html[] = 					'</div>';
		$html[] = 				'</div>';
		$html[] = 			'</div>';
		$html[] = 			'<div class="wysiwyg-mode-panel">';
		$html[] = 			'</div>';
		$html[] = 		'</div>';
		$html[] = 		'<div class="article-body">';
		$html[] = 			'<input type="hidden" name="section['.$key.'][type]" value="'.$this->_h($section->getType()).'" />';
		$html[] = 			'<input type="hidden" name="section['.$key.'][snippet]" value="'.$this->_h($section->getSnippet()).'" />';
		$html[] = 			'<input type="hidden" name="section['.$key.'][value]" value="'.$this->_h($section->getValue()).'" />';
		$html[] = 			'<input type="hidden" class="section_remove" name="section['.$key.'][remove]" value="0" />';
		
		$classes = array("m-area","liq-area","html-editor");
		if($section->getType() == "wysiwyg"){
			$classes[] = "aobata_editor";
		}else if($section->getType() == "preview"){
			$classes[] = "aobata_display";
		}else{
			$classes[] = "aobata_preview";
		}
		$style = ($section->getSectionHeight()) ? "height:" . $section->getSectionHeight() . "px" : "";
		
		$html[] = 		'<textarea name="section['.$key.'][content]" class="'.implode(" ",$classes).'" style="'.$style.'">'.$this->_h($section->getContent()).'</textarea>';
		$html[] = 		'</div><!-- // .article-body -->';
		$html[] = 		'<div class="article-footer">';
		$html[] = 			'<div class="panel">';
		$html[] = 				'<div class="panel-line">';
		$html[] = 					'<div class="panel-parts"><a class="icon-btn btn-orderup" href="javascript:void(0);" title="上と入れ換え"><em><strong>上と入れ換え</strong></em></a></div>';
		$html[] = 					'<div class="panel-parts"><a class="icon-btn btn-orderdown" href="javascript:void(0);" title="下と入れ換え"><em><strong>下と入れ換え</strong></em></a></div>';
		$html[] = 					'<div class="panel-parts do-remove"><a class="icon-xme-btn btn-delelement" href="javascript:void(0);" title="セクションを削除"><em><strong>セクションを削除</strong></em></a></div>';
		$html[] = 					'<div class="panel-parts undo-remove"><a class="icon-sh-btn btn-undo" href="javascript:void(0);" title="キャンセル"><em><strong>キャンセル</strong></em></a></div>';
		$html[] = 					'<div class="panel-parts close-editor" style="float:right;"><a class="icon-btn btn-close" href="javascript:void(0);" title="閉じる"><em>閉じる</em></a></div>';
		$html[] = 				'</div>';
		$html[] = 			'</div>';
		$html[] = 		'</div><!--  // .panel -->';
		$html[] = 	'</div><!-- // .section_list -->';
		
		return implode("",$html);
	}
	
	/**
	 * 要素の追加で呼ばれる画面を使用
	 */
	public static function bulidSectionMenus($orders = array(),$allow = array()){
		
		$inst = self::inst();
		
		$html = array();
		
		$list = $inst->getList($orders);
		$sections = array();
		$groups = array();
		
		foreach($list as $id => $snippet){
			if(!$snippet->getGroup()){
				if(in_array($id,$orders) && !in_array($id,$allow)){
					continue;
				}
				
				$sections[$id] = $snippet;
			}else{
				if(!isset($groups[$snippet->getGroup()]))$groups[$snippet->getGroup()] = array();
				$groups[$snippet->getGroup()][$id] = $snippet;
			}
		}
		
		foreach($sections as $id => $snippet){
			$child = (isset($groups[$id])) ? $groups[$id] : null ;
			
			$id = $inst->mode . "_" . $id;
			$html[] = $inst->buildSectionMenu($id,$snippet,$child);
		}
		
		
		return implode("\n",$html);
	}
	
	/**
	 * 上の挿入ボタン
	 */
	public static function buildInsertSectionMenus($orders,$allows){
		$inst = self::inst();
		$list = $inst->list;
		foreach($list as $key => $snippet){
			if($snippet->getType() != "wysiwyg"){
				unset($inst->list[$key]);
			}
		}
		$inst->mode = "insert";
		$html = self::bulidSectionMenus($orders,$allows);
		$inst->mode = "append";
		$inst->list = null;
		return $html;
	}
	
	function buildSectionMenu($key,$snippet,$child){
		$html = array();
		
		$id = $snippet->getId();
		
		$onclick = ($snippet->getForm()) ?
						"aobata_editor.show_new_section_form('$key');"
					: (($this->mode == "append") ? "aobata_editor.append_new_section" : "aobata_editor.insert_new_section") . '(this,\''.$snippet->getType().'\',\''.$id.'\');';
		
		$_class = ($snippet->getForm()) ? "has_form" : "";
		$parts_class = ($this->mode == "append") ? (($child) ? "icon-xsh-btn" : "icon-sh-btn") : "icon-btn";
		$funcName = (($this->mode == "append") ? "aobata_editor.append_new_section" : "aobata_editor.insert_new_section");
		$downpanel_class = ($this->mode == "append") ? "downarrow-btn" : "downarrow-s-btn";
		
		$html[] = '<div class="panel-parts '.$_class.'">';
		$html[] = '<div id="section_'.$key.'" class="downpanel">';
		$html[] = '<a onclick="'.$onclick.'" href="javascript:void(0);" class="'.$parts_class . " " . $snippet->getClass().'" title="'.$snippet->getName().'">';
		$html[] = '<em>'.$snippet->getName().'</em>';
		$html[] = '</a>';
		if($child){
			$html[] = '<a href="javascript:void(0);" class="'.$downpanel_class.'" title="'.$snippet->getName().'" onclick="aobata_editor.showOption(this);">';
			$html[] = '<em>'.$snippet->getName().'</em>';
			$html[] = '</a>';
		}
		$html[] = '</div>';
		
		
		
		if($snippet->getForm()){
			$html[] = '<div id="'.$key.'_section_form" class="downpanel-fix" style="display:none;">';
			$html[] = 	'<div class="downpanel-fix-inner">';
			$html[] = 		$snippet->buildForm();
			$html[] = 		'<div class="panel-line ri break">';
			$html[] = 			'<button class="section_form_btn m-btn" type="button" onclick="'.$funcName.'(this,\''.$snippet->getType().'\',\''.$id.'\',\'#'.$key.'_section_form\');">Add</button>';
			$html[] = 		'</div>';
			$html[] = 	'</div>';
			$html[] = '</div>';
		}
		
		
		
		if($child){
			$html[] = '<div id="section_'.$key.'_option" class="downmenu-layer" style="display:none;">';
			$html[] = '<ul class="menu">';
			foreach($child as $child_snippet){
				$html[] = '<li><a onclick="'.$funcName.'(this,\''.$child_snippet->getType().'\',\''.$child_snippet->getId().'\');" href="javascript:void(0);" title="'.$child_snippet->getName().'">'.$child_snippet->getName().'</a></li>';
			}
			$html[] = '</ul>';
			$html[] = '</div>';
		}
			
		$html[] = '</div>'; // .panel-parts
		
		return implode("",$html);
	}
	
	function getList($orders){
		if(!$this->list)$this->list = SOYCMS_Snippet::getList();
		$this->list = SOYCMS_Snippet::sortSnippet($this->list,$orders);
		return $this->list;
	}
	
	private function _h($value){
		return htmlspecialchars($value);
	}
}
?>