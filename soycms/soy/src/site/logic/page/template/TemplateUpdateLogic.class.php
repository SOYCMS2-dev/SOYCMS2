<?php
/**
 * テンプレートの更新周りのロジック
 */
class TemplateUpdateLogic extends SOY2LogicBase{
	
	/**
	 * テンプレートのHTML更新
	 */
	function updateTemplate($template,$options = array()){
		
		//まず保存
		$template->save();
		
		//Helper作成
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		
		//レイアウトを設定する
		$array = $logic->checkLayoutByTemplateContent($template);
		$template->setLayout($array);
		
		//要素を自動生成
		$logic->autoAppend($template,$options);
		
		//要素の場所を設定
		$logic->updateItemLayout($template);
		
		//再保存
		$template->save();
		
	}
	
	/**
	 * レイアウトの追加
	 */
	function addNewLayout($template,$layoutId){
		
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		$array = $template->getLayout();

		if(!is_array($array))$array = array();
		$array[$layoutId] = array(
			"id" => $layoutId,
			"name" => $layoutId,
			"color" => "#CCFFCC"
		);
		$template->setLayout($array);
		
		//要素の場所を設定
		$logic->updateItemLayout($template);
		
		$template->save();
		
	}
	
	/**
	 * 新しい要素を追加する
	 * 要素を追加し、テンプレート記載位置からソート順を設定する
	 */
	function addNewItems($template,$array){
		$items = $template->getItems();
			
		foreach($array as $layout => $array){
			foreach($array as $value){
				$obj = new SOYCMS_TemplateItem();
				$obj->setId($value);
				$obj->setLayout($layout);
				$items[$value] = $obj;
			}
		}
		
		$template->setItems($items);
		
		//並び替え
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		$logic->updateItemLayout($template);
		
		$template->save();
	}
	
	/**
	 * レイアウトの設定を更新
	 */
	function updateLayoutConfig($template,$array){
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		$layout = $logic->checkLayoutByTemplateContent($template);
		
		foreach($_POST["box"] as $key => $array){
			if(!isset($layout[$key]))continue;
			$layout[$key]["color"] = $array["color"];
		}
		
		$template->setLayout($layout);
		$template->save();
	}
	
}
?>