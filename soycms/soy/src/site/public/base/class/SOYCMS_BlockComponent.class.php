<?php

class SOYCMS_BlockComponent extends SOYBodyComponentBase{
	
	private $page;
	private $directory;
	private $block;
	
	function getStartTag(){
		$blockId = "[Block]";
		$id = $this->block->getId();
		$link = '@SOYCMS_ADMIN_ROOT_URL . "site/page/block/detail?path="';
		return parent::getStartTag() .
			'<?php SOYCMS_ItemWrapComponent::startTag("block",' .
			'"[Block]" . $'.$this->getParentPageParam().'["'.$id.'"]["block_name"] . "('.$this->block->getId().')",' .
			$link.'.$'.$this->getParentPageParam().'["'.$id.'"]["block_path"]); ?>';
	}
	
	function getEndTag(){
		$blockId = null;
		return '<?php SOYCMS_ItemWrapComponent::endTag(); ?>' .
				parent::getEndTag();
		
	}
	
	function execute(){
		
		$block = $this->getBlock();
		
		$this->addLabel("block_name",array(
			"html"=> $this->replaceText($block->getName()),
			"soy2prefix" => "cms"
		));
		
		//管理側で使う要素
		$this->addLink("block_path",array(
			"text"=> str_replace(SOYCMS_SITE_DIRECTORY,"",$block->getPath()),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("block_description",array(
			"html"=> $this->replaceText($block->getDescription()),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("block_index_title",array(
			"html"=> $this->replaceText($block->getIndexTitle()),
			"soy2prefix" => "cms",
			"visible" => (strlen($block->getIndexTitle())>0)
		));
		
		$this->addLink("block_index_link",array(
			"link"=> $this->replaceText($block->getIndexUrl()),
			"soy2prefix" => "cms"
		));
		
		//entry_listなどの追加
		$blockObj = $block->getObject();
		$blockObj->execute($this,$this->_soy2_parent);
		
		parent::execute();
	}
	
	function replaceText($text){
		$text = str_replace("#SiteName#",SOYCMS_DataSets::load("site_name",SOYCMS_SITE_ID),$text);
		$text = str_replace("#SiteUrl#",soycms_get_page_url("/"),$text);
		
		if(!$this->directory)return $text;
		$text = str_replace("#DirName#",$this->directory->getName(),$text);
		$text = str_replace("#PageName#",$this->page->getName(),$text);
		$text = str_replace("#BlockName#",$this->block->getName(),$text);
		
		$text = str_replace("#DirUrl#",soycms_get_page_url($this->directory->getUri()),$text);
		$text = str_replace("#PageUrl#",soycms_get_page_url($this->page->getUri()),$text);
		
		return $text;
	}

	function getBlock() {
		return $this->block;
	}
	function setBlock($block) {
		$this->block = $block;
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
}
?>