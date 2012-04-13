<?php
/**
 * テンプレートの要素を出力する
 */
class TemplateItemComponent extends HTMLPage{
	
	private $pageId;
	private $templateId = null;
	private $navigationId = null;
	private $type = "default";
	private $layoutArray = array();
	private $items = array();
	private $mode = "template";

	function TemplateItemComponent() {
		HTMLPage::HTMLPage();
	}
	
	function execute(){
		
		$this->createAdd("item_layout","HTMLLabel",array(
			"html" => self::buildLayout($this->getLayoutArray(),$this->templateId,$this->mode)
		));
		$this->createAdd("item_list","TemplateItemComponent_ItemList",array(
			"pageId" => $this->pageId,
			"list" => $this->getItems(),
			"mode" => $this->getMode(),
			"templateLink" => soycms_create_link("page/template/detail?id=" . $this->templateId)
		));
		
		$this->addLabel("mode_text",array(
			"text" => $this->getMode()
		));
		
		$this->addModel("trash_box",array(
			"visible" => ($this->mode == "page")
		));
		
		$this->addLabel("template_id",array("text" => $this->templateId));
		$this->addLabel("navigation_id",array("text"=>$this->navigationId));	
		
		parent::execute();
	}
	
	
	/**
	 * レイアウト用のコンテナを作成
	 */
	public static function buildLayout($layout,$templateId,$mode){
		if(!is_array($layout))$layout = array();
		$html = array();
		
		$html[] = '<div id="item_manager_div" class="item_manager">';
		
		if(empty($layout)){
//			$link = soycms_create_link("page/template/detail") . "?id=" . $templateId . "&auto#tpl_template";
//			
//			$html[] = '<div class="ce">';
//			$html[] = '<p class="xl break">レイアウトが作成されていません。</p>';
//			$html[] = '<p class="break"><a class="m-btn" href="'.$link.'">レイアウトを作成する</a></p>';
//			$html[] = '</div>';
			
			$layout = array(
				"blank" => array(
					"name" => "Untitle",
					"color" => "#EFEFEF"
				)
			);
		}
		
		$count = 0;
		foreach($layout as $key => $box){
			$id = "cell_" . $key;
			
			if(!is_array($box) || !isset($box["name"])){
				$box = array(
					"name" => "widget-" . $key, 
					"color" => "#CCFFCC"
				);
			}
			
			$html[] = '<div class="cell" style="background:'.$box["color"].'">'; 
			$html[] = 	'<div class="cell-title-wrap"> ';
			$html[] = 	'	<p class="cell-title">'.$box["name"].'</p> ';
			$html[] = 	'	<ul class="cell-title-btn"> ';
			
			if($mode != "navigation" && $key != "blank"){
				$html[] = 	'		<li class="colorSelector" title="背景色変更"><em><input type="hidden" name="box['.$key.'][color]" value="'.$box["color"].'" /></em></li> ';
				$html[] = 	'		<li class="close" title="閉じる"><span title="開く"></span></li>'; 
			}
			$html[] = 	'	</ul>'; 
			$html[] = 	'</div>'; 
			$html[] = 	'<div id="'.$id.'" class="inner" cell:pos="'.$key.'">'; 
			$html[] = 	'	<div class="drop_box">ここに追加する</div>'; 
			$html[] = 	'</div>'; 
			$html[] = '</div>';
			
			$count++; 
		}
		
		$html[] = '</div>';
		
		return implode("",$html);
		
	}



	/* getter setter */

	function getLayoutArray() {
		return $this->layoutArray;
	}
	function setLayoutArray($layoutArray) {
		$this->layoutArray = $layoutArray;
	}
		
	function getItems() {
		return $this->items;
	}
	function setItems($items) {
		$this->items = $items;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}

	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	
	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
	
	function getTemplateId() {
		return $this->templateId;
	}
	function setTemplateId($templateId) {
		$this->templateId = $templateId;
	}
	
	function getNavigationId() {
		return $this->navigationId;
	}
	function setNavigationId($navigationId) {
		$this->navigationId = $navigationId;
	}
	
}

class TemplateItemComponent_ItemList extends HTMLList{
	
	private $pageId;
	private $mode;
	private $detailLink;
	private $removeLink;
	private $templateLink;
	
	
	private $configLink;
	
	function init(){
		$this->configLink = soycms_create_link("/page/item/config");
	}
	
	function setList($list){
		
		uasort($list,create_function('$a,$b','return ($a->getOrder() >= $b->getOrder());'));
		parent::setList($list);
	}
	
	function populateItem($entity,$key){
		
		//詳細リンク
		$link = $entity->getConfigLink();
		if($this->pageId)$link .= "&pageId=" . $this->pageId;
		
		$this->addLink("detail_link",array(
			"link" => $link,
			"visible" => (strlen($entity->getConfigLink())>0)
		));
		
		$this->addLink("config_link",array(
			"link" => $this->configLink . "?id=".$entity->getId()."&type=".$entity->getType()."&pageId=" . $this->pageId,
			"visible" => ($this->mode == "page")
		));
		
		//コピーリンク
		$link = $entity->getCopyLink();
		if($this->pageId)$link .= "&pageId=" . $this->pageId;
		
		$this->addLink("copy_link",array(
			"link" => $link,
			"visible" => (strlen($entity->getCopyLink())>0)
		));
		
		//非表示リンク
		$this->addModel("trash_link",array(
			"visible" => ($this->mode == "page")
		));
			
		//削除リンク
		$this->addModel("remove_link",array(
			"visible" => ($this->mode != "page")
		));	
		
		//復元リンク
		$this->addModel("recover_link",array(
			"style" => (!$entity->getDeleted()) ? "display:none;" : ""
		));
		
		//一括表示、一括非表示
		$this->addModel("whole_show_link_wrap",array(
			"visible" => ($this->mode != "page") 
		));
		$this->addLink("whole_show_link",array(
			"link" =>  $this->templateLink . "&item=" . $entity->getType() . ":" .$entity->getId() . "&toggle=1"
		));
		$this->addModel("whole_hide_link_wrap",array(
			"visible" => ($this->mode != "page") 
		));
		$this->addLink("whole_hide_link",array(
			"link" =>  $this->templateLink . "&item=" . $entity->getType() . ":" .$entity->getId() . "&toggle=0"
		));
		
		$this->createAdd("block_id","HTMLLabel",array(
			"text" => $key
		));
		
		$className = "";
		if($entity->getType() == "library"){
			$className = "boxcolor3";
		}else if($entity->getType() == "navigation"){
			$className = "boxcolor1";
		}else if($entity->getType() == "block"){
			$className = "boxcolor2";
		}else if($entity->getType() == "default"){
			$className = "boxcolor4";
		}
		
		$this->addModel("item_box",array(
			"attr:pos" => $entity->getLayout(),
			"attr:deleted" => (int)$entity->getDeleted(),
			"class" => "item_box " . $className,
		));
		
		$this->addLabel("item_name",array(
			"text" => $entity->getName()
		));
		
		$this->addLabel("item_type_text",array(
			"text" => $entity->getTypeText()
		));
		
		$this->addLabel("item_description",array(
			"html" => $entity->getComment()
		));
		
		$this->addInput("item_position",array(
			"name" => "Layout[".$key."]",
			"value" => $entity->getLayout()
		));
		
		$this->addInput("item_order",array(
			"name" => "LayoutOrder[".$key."]",
			"value" => $entity->getOrder(),
		));
		
		//削除フラグ
		$this->addInput("item_delete",array(
			"name" => "ItemDelete[".$key."]",
			"value" => (int)$entity->getDeleted(),
		));
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}

	function getTemplateLink() {
		return $this->templateLink;
	}
	function setTemplateLink($templateLink) {
		$this->templateLink = $templateLink;
	}
}
?>