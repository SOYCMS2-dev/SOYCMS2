<?php
/**
 * フィードを出力する用のページ
 */
class SOYCMS_FeedPage extends SOYCMS_PageBase{
	
	private $directory;
	private $limit = 10;
	private $excerpt = 100;
	private $type = "excerpt";
	
	public static function getDefaultBlocks(){
		return array(
			"entry_list" => "記事一覧",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
	/* getter setter */

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}

	function getExcerpt() {
		return $this->excerpt;
	}
	function setExcerpt($excerpt) {
		$this->excerpt = $excerpt;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
}
?>