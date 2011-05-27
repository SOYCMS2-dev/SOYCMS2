<?php
/**
 * @title テンプレートの作成
 */
class page_page_template_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateUpdateLogic");
		$suffix = "tpl_config";
		
		
		//一括切り替え
		if(isset($_POST["toggle"])){
			$suffix = "tpl_item";
			$itemId = $_POST["item"];
			
			SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper")
				->toggleItemConfig($this->template->getId(),$itemId,$_POST["toggle"]);
				
			$this->jump("/page/template/check?id=" . $this->id . "&updated&suffix=" . $suffix);	
		}
		
		
		
		$options = array();
		$options["is_overwrite"] = @$_POST["is_overwrite"];
		
		//新しいブロックの追加
		if(isset($_POST["new_block"]) && strlen(@$_POST["new_block_id"])>0){
			$this->jump("/page/block/create?template=" . $this->id . "&id=" . $_POST["new_block_id"] . "&type=" . $_POST["new_block_type"]);
			exit;
		}
		
		//テンプレートの設定
		if(isset($_POST["save_config"]) && $_POST["Template"]){
			SOY2::cast($this->template,$_POST["Template"]);
			$this->template->save(); 
		}
		
		//テンプレートの保存
		if(isset($_POST["save_template"]) && $_POST["Template"]){
			SOY2::cast($this->template,$_POST["Template"]);
			
			//HTML変更イベントを呼び出し
			$logic->updateTemplate($this->template,$options);
			
			SOYCMS_History::addHistory("template",$this->template);
		}
		
		//レイアウトの追加
		if(isset($_POST["new_skelton"]) && isset($_POST["add_new_skelton"])){
			$id = $_POST["new_skelton"];
			$logic->addNewLayout($this->template,$id);
		}
		
		//色の設定
		if(isset($_POST["save_item"]) && isset($_POST["box"])){
			$logic->updateLayoutConfig($this->template,$_POST["box"]);
			$suffix = "tpl_item";
		}
		
		//新しい要素の追加
		if(isset($_POST["NewItem"]) && count($_POST["NewItem"]) > 0){
			$logic->addNewItems($this->template,$_POST["NewItem"]);
			$suffix = "tpl_item";
		}
		
		//プロパティの保存
		if(isset($_POST["save_property"]) && isset($_POST["Template"])){
			//テンプレートのプロパティ
			$content = $_POST["Template"]["property"];
			file_put_contents($this->template->getPropertyFilePath(),$content);
		}
		
		//要素の削除時
		//削除(id = 1)が1個以上あるときのみ
		if(isset($_POST["ItemDelete"]) && array_sum($_POST["ItemDelete"]) > 0){
			$items = $this->template->getItems();
			$html = $this->template->loadTemplate();
			
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
			$this->template->setItems($items);
			$this->template->setTemplate($html);
			$this->template->save();
		}
		
		$this->jump("/page/template/check?id=" . $this->id . "&updated&suffix=" . $suffix);
	}
	
	private $id;
	private $template;
	
	function prepare(){
		$this->id = @$_GET["id"];
		$this->template = SOYCMS_Template::load($this->id);
		if(!$this->template){
			$this->jump("/page/template");
		}
		
		parent::prepare();
	}

	function page_page_template_detail(){
		WebPage::WebPage();
		
		$this->createAdd("form","_class.form.TemplateForm",array(
			"template" => $this->template
		));
		
		
		$this->addLabel("template_name_text",array(
			"text" => $this->template->getName()
		));
		
		
		$this->addTextArea("template_content",array(
			"name" => "Template[template]",
			"value" => $this->template->loadTemplate()
		));
		
		$this->addTextArea("template_property",array(
			"name" => "Template[property]",
			"value" => @file_get_contents($this->template->getPropertyFilePath())
		));
		
		$this->addLabel("layout_config",array(
			"html" => json_encode($this->template->getLayout()) 
		));
		
		$this->addLink("preview_link",array(
			"link" => SOYCMS_SITE_URL . "?template_preview=" . $this->template->getId() . "&SOYCMS_SSID=" . session_id()
		));
		
		$pages = SOY2DAO::find("SOYCMS_Page",array("template" => $this->template->getId()));
		$this->addList("page_list",array(
			"list" => $pages,
			'populateItem:function($entity)' => '$this->addLink("page_public_link",array("link"=>soycms_union_uri("'.SOYCMS_SITE_URL.'",$entity->getUri())));' .
					'$this->addLink("page_link",array("link"=>"'.soycms_create_link("page/detail/") .'".$entity->getId(),"text"=>$entity->getName()));'
		));
		$this->addModel("no_page",array(
			"visible" => (count($pages)<1)
		));
		
		$this->addLink("pages_link",array(
			"link" => soycms_create_link("page/template/pages?id=" . $this->template->getId())
		));
		
		$this->addModel("created",array("visible" => (isset($_GET["created"]))));
		
		$this->createAdd("history_index","page.history.page_page_history_index",array(
			"type" => "template",
			"name" => $this->template->getName(),
			"objectId" => $this->id
		));
	}
}