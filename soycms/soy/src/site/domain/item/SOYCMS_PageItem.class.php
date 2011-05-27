<?php
SOY2::import("site.domain.item.SOYCMS_HTMLItem");

/**
 * container class for a part of template  
 */
class SOYCMS_PageItem extends SOYCMS_HTMLItem{
	
	/**
	 * ページの設定からブロックを取得、無ければテンプレートの設定を取得
	 * ブロックの設定側で読み込め無い場合があるので、そのあたりの対策は必要
	 */
	public static function getBlock(SOYCMS_Page $page,$blockId,$param = false){
		$blockDir = $page->getPageDirectory() . "block/";
		if(!file_exists($blockDir)){
			mkdir($blockDir,0755);
		}
		
		$block = SOYCMS_Block::load($blockId,$blockDir);
		
		if(!$block){
			$block = SOYCMS_Block::load($blockId,SOYCMS_Template::getTemplateDirectory() . $page->getTemplate() . "/block/");
			
			//管理側からの取得
			if(!$param){
				$new_block = SOYCMS_Block::create($blockId, $blockDir);
				$path = $new_block->getPath();
				SOY2::cast($new_block, $block);
				$new_block->setPath($path);
				$block = $new_block; 
			}else{
				$block->setPath($block->getPath() . "&page=" . $page->getId() . "&id=" . $blockId);
			}
		}else{
			if($param){
				$block->setPath($block->getPath() . "&page=" . $page->getId() . "&id=" . $blockId);
			}
		}
		
		return $block;
	}
	
	/**
	 * ページのブロック設定を読み込む
	 */
	function loadPageBlockConfig($page,$blockId){
		$blockDir = $page->getPageDirectory() . "block/";
		if(!file_exists($blockDir)){
			mkdir($blockDir,0755);
		}
		
		$block = SOYCMS_Block::load($blockId,$blockDir);
		
		//上書きされている場合
		if($block){
			$this->setName($block->getName() . "*");
			$this->setComment($block->getDescription());
		}
	}
	
	
	private $pageId;
	
	function getConfigLink(){
		if($this->getType() == "block"){
			return soycms_create_link("/page/block/detail?page=" . $this->pageId . "&id=" . $this->getId());
		}
		return parent::getConfigLink();
	}	

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}
?>