<?php
/**
 * @title ナビゲーションの作成
 */
class page_page_navigation_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		//新しいブロックの追加
		if(isset($_POST["new_block"]) && strlen(@$_POST["new_block_id"])>0){
			$this->jump("/page/block/create?navigation=" . $this->id . "&id=" . $_POST["new_block_id"] . "&type=" . $_POST["new_block_type"]);
			exit;
		}
		
		if(isset($_POST["NewItem"]) && count($_POST["NewItem"])>0){
			$items = $this->navigation->getItems();
			
			foreach($_POST["NewItem"] as $layout => $array){
				foreach($array as $value){
					$item = new SOYCMS_NavigationItem();
					$item->setId($value);
					$item->setNavigationId($this->navigation->getId());
					$item->setLayout($layout);
					$items[$value] = $item;
				}
			}
			$this->navigation->setItems($items);
			$this->navigation->save();
			
		}
		
		if(isset($_POST["Navigation"])){
			SOY2::cast($this->navigation,(object)$_POST["Navigation"]);
			$this->navigation->save();
			
			$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
			
			
			if(isset($_POST["LayoutOrder"])){
				$logic->convertTemplateByOrder($this->navigation,array_keys($_POST["LayoutOrder"]));
			}
			
			$logic->autoAppend($this->navigation);
			
			$logic->updateItemOrder($this->navigation);
			
			$this->navigation->save();
			
			SOYCMS_History::addHistory("navigation",$this->navigation);
			
		}
		
		if(isset($_POST["ItemDelete"])){
			$items = $this->navigation->getItems();
			$html = $this->navigation->loadTemplate();
			
			foreach($_POST["ItemDelete"] as $key => $value){
				if($value && isset($items[$key])){
					$deleteItem = $items[$key];
					unset($items[$key]);
					
					$start = '/<!--\s*'.$deleteItem->getFormat().'\s*[\S]*-->/';
					$end = '/<!--\s*\/'.$deleteItem->getFormat().'\s*[\S]*-->/';
					
					//削る
					if(preg_match($start,$html,$tmp1,PREG_OFFSET_CAPTURE)
						&& preg_match($end,$html,$tmp2,PREG_OFFSET_CAPTURE)
					){
						$startOffset = $tmp1[0][1];
						$endOffset = $tmp2[0][1] + strlen($tmp2[0][0]);
						
						$tmp = substr($html,0,$startOffset);
						$tmp .= substr($html,$endOffset);
						$html = $tmp;
					}
				}
			}
			
			$this->navigation->setItems($items);
			$this->navigation->setTemplate($html);
			$this->navigation->save();
		}
		
		
		
		$this->jump("/page/navigation/check?id=" . $this->id . "&updated");
	}
	
	private $id;
	private $navigation;
	
	function prepare(){
		$this->id = $_GET["id"];
		$this->navigation = SOYCMS_Navigation::load($this->id);
		if(!$this->navigation){
			$this->jump("/page/navigation");
		}
		
		$items = $this->navigation->getItems();
		foreach($items as $key => $item){
			$items[$key]->setLayout($this->id);
		}
		$this->navigation->setItems($items);
		
		parent::prepare();
	}

	function page_page_navigation_detail(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.NavigationForm",array(
			"navigation" => $this->navigation
		));
		
		
		$this->addLabel("navigation_name_text",array(
			"text" => $this->navigation->getName()
		));
		
		$this->addLink("copy_link",array(
			"link" => soycms_create_link("page/navigation/create?id=") . $this->id
		));
		
		$this->createAdd("history_index","page.history.page_page_history_index",array(
			"type" => "navigation",
			"name" => $this->navigation->getName(),
			"objectId" => $this->id
		));
	}
}