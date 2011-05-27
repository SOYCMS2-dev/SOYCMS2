<?php
SOY2::import("site.domain.item.SOYCMS_HTMLItem");

/**
 * container class for a part of template  
 */
class SOYCMS_TemplateItem extends SOYCMS_HTMLItem{
	
	private $templateId;
	
	public static function getTypes(){
		return array(
			"block" => "ブロック",
			"library" => "ライブラリ",
			"navigation" => "ナビゲーション",
		);
	}
	
	function getConfigLink(){
		if($this->getType() == "block"){
			return soycms_create_link("/page/block/detail?template=" . $this->templateId . "&id=" . $this->getId());
		}
		return parent::getConfigLink();
	}
	
	/**
	 * 自動生成する
	 */
	function generate($template,$options = array()){
		if(!$this->check())return false;
		
		$this->setTemplateId($template->getId());
		
		switch($this->getType()){
			case "block":
				
				$dir = SOYCMS_Template::getTemplateDirectory() . $this->getTemplateId() . "/block/";
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
			case "navigation":
				break;
			default:
				return false;
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
				
				$block = SOYCMS_Block::load($this->getId(),SOYCMS_Template::getTemplateDirectory() . $this->getTemplateId() . "/block/");
				
				//無かったら自動生成
				if(!$block){
					$dir = SOYCMS_Template::getTemplateDirectory() . $this->getTemplateId() . "/block/";
					if(!file_exists($dir)){
						soy2_mkdir($dir,0755);
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
			case "navigation":
				
				$navi = SOYCMS_Navigation::load($this->getId());
				if($navi){
					$this->setName($navi->getName());
					$this->setComment($navi->getDescription());
				}
				
				$this->object = $navi;
			
				break;
			default:
				return parent::prepare();
				break;
		}
		
	}
	
	/* getter setter */
	
	function getTemplateId() {
		return $this->templateId;
	}
	function setTemplateId($templateId) {
		$this->templateId = $templateId;
	}

	
}
?>