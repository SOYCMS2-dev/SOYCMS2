<?php

class SOYCMS_SitePage extends SOYCMS_PageBase{
	
}

class SOYCMS_DefaultPage extends SOYCMS_PageBase{
	
	/**
	 * 標準で追加されるブロックを表示
	 */
	public static function getDefaultBlocks(){
		return array(
			"entry" => "記事を表示",
			"directory_label_list" => "ディレクトリラベル一覧ブロック",
		);
	}
		
}
?>