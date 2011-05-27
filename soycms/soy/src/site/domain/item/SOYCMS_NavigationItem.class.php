<?php
SOY2::import("site.domain.item.SOYCMS_HTMLItem");

/**
 * container class for a part of template  
 */
class SOYCMS_NavigationItem extends SOYCMS_HTMLItem{

	private $navigationId;
	
	public static function getTypes(){
		return array(
			"block" => "ブロック",
			"library" => "ライブラリ",
		);
	}
	
	function getConfigLink(){
		if($this->getType() == "block"){
			return soycms_create_link("/page/block/detail?navigation=" . $this->navigationId . "&id=" . $this->getId());
		}
		return parent::getConfigLink();
	}
	
	/**
	 * 自動生成する
	 */
	function generate($navi,$options = array()){
		if(!$this->check())return false;
		$this->setNavigationId($navi->getId());
		
		switch($this->getType()){
			case "block":
			
				$dir = SOYCMS_Navigation::getNavigationDirectory() . $this->getNavigationId() . "/block/";
				if(!file_exists($dir)){
					mkdir($dir,0755);
				}
				
				$block = SOYCMS_Block::create($this->getId(), $dir, $options);
				$block->setName($this->getName());
				$block->setDescription($this->getComment());
				$block->save();
			
				break;
			case "library":
				
			
				$library = new SOYCMS_Library();
				$library->setId($this->getId());
				$library->setName($this->getName());
				$library->setDescription($this->getComment());
				$library->save();
				
				break;
			default:
				return;
				break;
		}
		
		return true;
	}
	
	/**
	 * 読み込む
	 */
	function prepare(){
		
		switch($this->getType()){
			case "block":
			
				$block = SOYCMS_Block::load($this->getId(),SOYCMS_Navigation::getNavigationDirectory() . $this->getNavigationId() . "/block/");
				
				if(!$block){
					$dir = SOYCMS_Navigation::getNavigationDirectory() . $this->getNavigationId() . "/block/";
					if(!file_exists($dir)){
						$res = @mkdir($dir,0755);
						if(!$res)return;
					}
					
					$block = SOYCMS_Block::create($this->getId(), $dir);
					$block->setName($this->getName());
					$block->setDescription($this->getComment());
					$block->save();
				}
				
				if($block){
					$this->setName($block->getName());
					$this->setComment($block->getDescription());
				}
				
				$this->object = $block;
			
				break;
			case "library":
				$library = SOYCMS_Library::load($this->getId());
				if($library){
					$this->setName($library->getName());
					$this->setComment($library->getDescription());
				}
				
				$this->object = $library;
				
				break;
			default:
				return parent::prepare($library);
				break;
		}
		
	}
	
	function check(){
		if(strlen($this->getId())<1)return false;
		if(strlen($this->getName())<1)return false;
		
		return parent::check();
	}
	
	/* getter setter */

	function getNavigationId() {
		return $this->navigationId;
	}
	function setNavigationId($navigationId) {
		$this->navigationId = $navigationId;
	}
}
?>