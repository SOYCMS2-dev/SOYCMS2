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
						"show_new_section_form('$key');"
					: (($this->mode == "append") ? "append_new_section" : "insert_new_section") . '(this,\''.$snippet->getType().'\',\''.$id.'\');';
		
		$_class = ($snippet->getForm()) ? "has_form" : "";
		$parts_class = ($this->mode == "append") ? (($child) ? "icon-xsh-btn" : "icon-sh-btn") : "icon-btn";
		$funcName = (($this->mode == "append") ? "append_new_section" : "insert_new_section");
		$downpanel_class = ($this->mode == "append") ? "downarrow-btn" : "downarrow-s-btn";
		
		$html[] = '<div class="panel-parts '.$_class.'">';
		$html[] = '<div id="section_'.$key.'" class="downpanel">';
		$html[] = '<a onclick="'.$onclick.'" href="javascript:void(0);" class="'.$parts_class . " " . $snippet->getClass().'" title="'.$snippet->getName().'">';
		$html[] = '<em>'.$snippet->getName().'</em>';
		$html[] = '</a>';
		if($child){
			$html[] = '<a href="javascript:void(0);" class="'.$downpanel_class.'" title="'.$snippet->getName().'" onclick="_showoption(this);">';
			$html[] = '<em>'.$snippet->getName().'</em>';
			$html[] = '</a>';
		}
		$html[] = '</div>';
		
		
		
		if($snippet->getForm()){
			$html[] = '<div id="'.$key.'_section_form" class="downpanel-fix" style="display:none;width:550px;">';
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
}
?>