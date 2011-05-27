<?php

class BlockForm extends HTMLForm{
	
	private $block;
	
	function init(){
	}

	function execute() {
		$this->buildForm($this->getBlock());
		parent::execute();
		
	}
	
	/**
	 * フォームの構築
	 */
	function buildForm($block){
		
		$this->addLabel("block_id_text",array(
			"text" => $block->getId()
		));
		
		//ライブラリ名
		$this->addInput("block_name",array(
			"name" => "Block[name]",
			"value" => $block->getName()
		));
		
		//説明
		$this->addTextArea("block_description",array(
			"name" => "Block[description]",
			"value" => $block->getDescription()
		));
		
		//一覧リンク
		$this->addInput("block_index_title",array(
			"name" => "Block[indexTitle]",
			"value" => $block->getIndexTitle()
		));
		
		//説明
		$this->addInput("block_index_url",array(
			"name" => "Block[indexUrl]",
			"value" => $block->getIndexUrl()
		));
		
		$this->addLabel("block_config",array(
			"html" => $block->getObject()->getForm()
		));
		
	}

	function getBlock() {
		return $this->block;
	}
	function setBlock($block) {
		$this->block = $block;
	}
}

?>