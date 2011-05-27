<?php
/**
 * @title ナビゲーションの作成
 */
class page_page_navigation_check extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Navigation"])){
			$navigation = $_POST["Navigation"]["template"];
			$this->navigation->setTemplate($navigation);
			$this->navigation->save();
			
			$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
			$logic->autoAppend($this->navigation);
			$logic->updateItemOrder($this->navigation);
		}
		
		if(isset($_POST["remove"])){
			$items = $this->navigation->getItems();
			$key = $this->item->getType() . ":" . $this->item->getId();
			if(isset($items[$key])){
				$this->item->remove();
				unset($items[$key]);
			}
			$this->navigation->setItems($items);
		}
		
		$this->navigation->save();
		
		$this->jump("/page/navigation/check?id=" . $this->id . "&updated");
		
	}
	
	private $id;
	private $navigation;
	private $item;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->navigation = SOYCMS_Navigation::load($this->id);
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		
		$this->navigation->loadTemplate();
		
		//if ok
		$blankItems = $logic->checkItem($this->navigation);
		if(count($blankItems) < 1){
			$this->jump("/page/navigation/detail?id=" . $this->id . "&updated");
		} 
		$this->item = $blankItems[0];
		
		parent::prepare();
	}

	function page_page_navigation_check(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("item_name",array(
			"text" => $this->item->getName()
		));
	}
	
	function buildForm(){
		$this->addForm("form");
		
		$this->addTextArea("start_tag",array(
			"text" => "<!-- " . $this->item->getFormat() . " -->"
		));
		$this->addTextArea("end_tag",array(
			"text" => "<!-- /" . $this->item->getFormat() . " -->"
		));
		
		//全体タグ
		$this->addTextArea("whole_tag",array(
			"text" => (!$this->item) ? ""
			: "<!-- " . $this->item->getFormat() . " -->\n" .
				$this->getItemContent($this->item) . 
			"\n<!-- /" . $this->item->getFormat() . " -->"
		));
		
		$this->addModel("is_item",array(
			"visible" => ($this->item)	/* コピーしてきたけど、navigationには今のところレイアウトの追加は無い */
		));
		
		//テンプレート本体
		$this->addTextArea("navigation_content",array(
			"name" => "Navigation[template]",
			"text" => $this->navigation->loadTemplate()
		));
	}
	
	/**
	 * 要素の中身を取得する
	 */
	function getItemContent($item){
		$html = "";
		$item->prepare();
		$object = $item->getObject();
		
		if($object){
			$html = $object->getPreview();
		}
		
		
		
		return $html;
	}
}
