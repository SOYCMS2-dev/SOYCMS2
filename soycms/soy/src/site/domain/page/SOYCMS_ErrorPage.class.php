<?php

class SOYCMS_ErrorPage extends SOYCMS_PageBase{
	
	private $statusCode = 404;
	
	/**
	 * 標準で追加されるブロックを表示
	 */
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事を表示",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
	
	function getHeaders(){
		return array(
			403 => "403 Forbidden",
			404 => "404 Not Found",
			500 => "500 Internal Server Error",
			503 => "503 Service Unavailable"
		);
	}
	
	function save(){
		
		$entries = SOY2DAO::find("SOYCMS_Entry",array("directory" => $this->getPage()->getId()));
		if(empty($entries)){
			$contents = array(
				403 => "<p>この記事は非公開です。</p>",
				404 => "<p>存在しないURLです。</p>",
				500 => "<p>エラーが発生しました。</p>",
				503 => "<p>エラーが発生しました。</p>",
			);
			$page = $this->getPage();
			
			$entry = new SOYCMS_Entry();
			$entry->setTitle($page->getName());
			$entry->setContent($contents[$this->statusCode]);
			$entry->setDirectory($page->getId());
			$entry->setPublish(1);
			$entry->setStatus("open");
			$entry->save();
			
		}
		
		parent::save();
	}
	
	/* getter setter */
	
	

	function getStatusCode() {
		return $this->statusCode;
	}
	function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}
}
