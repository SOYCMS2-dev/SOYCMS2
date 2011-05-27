<?php
/**
 * @title テンプレートの作成
 */
class page_page_template_check extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["Template"])){
			$template = $_POST["Template"]["template"];
			$this->template->setTemplate($template);
		}
		
		if(isset($_POST["Layout"])){
			foreach($_POST["Layout"] as $key => $html){
				$this->template->setTemplateByLayout($key,$html);
			}
		}
		
		//要素を取り除く
		if(isset($_POST["remove"])){
			$items = $this->template->getItems();
			
			if($this->item){
				$key = $this->item->getType() . ":" . $this->item->getId();
				if(isset($items[$key]))unset($items[$key]);
				$this->template->setItems($items);
			}else{
				$key = @$this->skelton["id"];
				$layout = $this->template->getLayout();
				if(isset($layout[$key])){
					unset($layout[$key]);
				}else{
					$layout = array();
				}
				$this->template->setLayout($layout);
			}
		}
		
		$this->template->save();
		
		//テンプレートを修正したので並び順を修正
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		$logic->updateItemOrder($this->template);
		$this->template->save();
		
		$query = array();
		if(strlen(@$_GET["suffix"])>0)$query["suffix"] = $_GET["suffix"];
		$query["updated"] = "updated";
		
		if(isset($_GET["page"])){
			$query["page"] = $_GET["page"];
			$this->jump("/page/template/check?id=" . $this->id . "&" . http_build_query($query));
		}else{
			$this->jump("/page/template/check?id=" . $this->id . "&" . http_build_query($query));
		}
		
	}
	
	private $id;
	private $template;
	private $item;
	
	function prepare(){
		
		$this->id = $_GET["id"];
		$this->template = SOYCMS_Template::load($this->id);
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		
		//if ok
		$blankItems = $logic->checkItem($this->template);
		$blankSkelton = $logic->checkLayout($this->template);
		
		if(empty($blankItems) && empty($blankSkelton)){
			$suffix = (strlen(@$_GET["suffix"])>0) ? "#" . $_GET["suffix"] : "#tpl_config";
			
			if(isset($_GET["page"])){
				$this->jump("/page/detail/" . (int)$_GET["page"] . $suffix);	//要素の追加の場合
			}else if(isset($_GET["created"])){
				$this->jump("/page/template/detail?id=" . $this->id ."&created&". $suffix);
			}else{
				$this->jump("/page/template/detail?id=" . $this->id ."&updated&". $suffix);
			}
		} 
		
		if(count($blankItems)>0){
			$this->item = $blankItems[0];
		}else{
			$this->skelton = $blankSkelton[0];
		}
		
		parent::prepare();
	}

	function page_page_template_check(){
		WebPage::WebPage();
		
		$this->buildPage();
		$this->buildForm();
		
	}
	
	function buildPage(){
		$this->addLabel("item_name",array(
			"text" => ($this->item) ? $this->item->getName() : @$this->skelton["name"]
		));
	}
	
	function buildForm(){
		$this->addForm("form");
		
		$this->addTextArea("start_tag",array(
			"text" => ($this->item) ? "<!-- " . $this->item->getFormat() . " -->"
						: "<!-- layout:" . @$this->skelton["name"] . " -->"
		));
		$this->addTextArea("end_tag",array(
			"text" => ($this->item) ? "<!-- /" . $this->item->getFormat() . " -->" 
					: "<!-- /layout:" . @$this->skelton["name"] . " -->"
		));
		
		$this->addTextArea("whole_tag",array(
			"text" => ($this->item) ? "<!-- " . $this->item->getFormat() . " -->\n" . 
				$this->getItemContent($this->item) . 
				"\n<!-- /" . $this->item->getFormat() . " -->" : ""
		));
		
		$this->addModel("is_item",array(
			"visible" => ($this->item)
		));
		
		//テンプレート本体
		$this->addTextArea("template_content",array(
			"name" => "Template[template]",
			"text" => $this->template->getTemplate()
		));
		
		$layout = ($this->item) ? $this->item->getLayout() : null;
		if(!$this->template->hasLayout($layout))$layout = null;
		$this->addTextArea("layout_template_content",array(
			"name" => "Layout[".$layout."]",
			"value" => ($layout) ? $this->template->getTemplateByLayout($layout) : ""
		));
		$this->addLabel("layout_id",array(
			"text" => $layout
		));
		
		$layouts = $this->template->getLayout();
		$this->addModel("layout_box",array(
			"visible" => (strlen($layout)>0),
			"style" => (isset($layouts[$layout]))? "background-color:" . $layouts[$layout]["color"] : ""
		));
		
		
		//レイアウトが指定されている場合はレイアウトだけにする
		$this->addModel("no_layout",array(
			"visible" => (strlen($layout)<1)
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
		
		switch($item->getId()){
			case "comment_form":
				$html = '<form id="comment-form" cms:id="comment_form">
<dl>
	<dt><label for="comment-title">タイトル</label></dt>
	<dd><input cms:id="comment_form_title"  name="title" id="comment-title" type="text" size="32" value="" /></dd>
	<dt><label for="comment-author">お名前</label></dt>
	<dd><input cms:id="comment_form_author" name="author" id="comment-author" type="text" size="32" value="" /></dd>
	<dt><label for="comment-url">URL</label></dt>
	<dd><input cms:id="comment_form_url" name="url" type="text" id="comment-url" value="" size="32" /></dd>
	<dt><label for="comment-body">コメント</label></dt>
	<dd><textarea cms:id="comment_form_content" name="body" id="comment-body" rows="6" cols="40" value=""></textarea></dd>
</dl>
	<p class="submit"><input type="submit" value="コメント投稿" id="comment-submit" name="comment-submit" class="btn" /></p>
</form>';
				break;
			case "comment_list":
				$html = '<dt class="title" cms:id="comment_title">コメントタイトル</dt>
<dt class="data">
	<span class="author">
		<a cms:id="comment_url_link"><!-- cms:id="comment_author" /--></a>
	</span>
	<span class="date" cms:id="comment_submit_date" cms:format="Y-m-d H:i">YYYY-MM-DD HH:II</span>
</dt>
<dd>
	<p>
	<!-- cms:id="comment_content" -->
	テキストテキストテキストテキストテキストテキストテキスト
	<!-- /cms:id="comment_content" -->
	</p>
</dd>';
				break;
		}
		
		return $html;
	}
	
}
