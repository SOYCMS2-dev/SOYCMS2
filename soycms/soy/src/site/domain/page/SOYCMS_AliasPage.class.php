<?php
/**
 * 特定のディレクトリのエイリアスを作る
 */
class SOYCMS_AliasPage extends SOYCMS_PageBase{
	
	private $directory;
	
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/" . __CLASS__ . "/AliasPageFormPage.class.php");
		$webPage = SOY2HTMLFactory::createInstance("AliasPageFormPage",array(
			"arguments" => $this
		));
		$webPage->main();
		
		ob_start();
		$webPage->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 標準で追加されるブロックを表示 DetailPageと揃える
	 */
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事詳細を表示",
			"comment_form" => "コメントフォーム",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
	/**
	 * テンプレートは詳細と同じ
	 */
	function getTemplateType(){
		return "detail";
	}
	
	
	/* getter setter */
   

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
}
?>