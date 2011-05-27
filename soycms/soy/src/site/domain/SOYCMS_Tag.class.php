<?php
/**
 * @table soycms_site_tag
 */
class SOYCMS_Tag extends SOY2DAO_EntityBase{
	
	/**
	 * @column entry_id
	 */
	private $entryId;
	
	/**
	 * @column tag_text
	 */
	private $tag;
	
	/**
	 * @column hash_text
	 */
	private $hash;
	
	/**
	 * @column display_order
	 */
	private $order = 0;
	
	function check(){
		if(strlen($this->tag)<1)return false;
		return true;
	}
	
	function __toString(){
		return $this->tag;
	}
	
	/* 便利メソッド */
	public static function getByEntryId($entryId){
		$dao = SOY2DAOFactory::create("SOYCMS_TagDAO");
		$list = $dao->getByEntryId($entryId);
		return $list;
	}
	
	public static function updateTags($entryId,$tags){
		$dao = SOY2DAOFactory::create("SOYCMS_TagDAO");
		
		//clear
		$dao->deleteByEntryId($entryId);
		
		foreach($tags as $key => $tag){
			
			$tag = trim($tag);
			
			$obj = new SOYCMS_Tag();
			$obj->setTag($tag);
			$obj->setEntryId($entryId);
			$obj->setOrder($key);
			$dao->insert($obj);
			
		}
	}
	
	/**
	 * 記事からラベルを削除
	 * 
	 */
	public static function clearTag($entryId){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTagDAO");
		$dao->deleteByEntryId($entryId);
	}
	
	/**
	 * 検索してタグをつける
	 */
	public static function autoTags($entryId,$contents){
		$contents = strip_tags($contents);
		$contents = str_replace(array(" ","\n","\r"),"",$contents);
		
		$dao = SOY2DAOFactory::create("SOYCMS_TagDAO");
		$dao->deleteByEntryId($entryId);
		
		$res = array();
		$tags = $dao->getTagList();
		
		foreach($tags as $tag){
			if(strpos($contents,$tag) !== false){
				$res[] = $tag;
			}
		}
		
		$counter = 0;
		foreach($res as $tag){
			$obj = new SOYCMS_Tag();
			$obj->setTag($tag);
			$obj->setEntryId($entryId);
			$obj->setOrder($counter);
			$dao->insert($obj);
			
			$counter++;
		}
		
	}
	
	public static function getTagList(){
		$dao = SOY2DAOFactory::create("SOYCMS_TagDAO");
		
		$res = array();
		$tags = $dao->getTagList();
		return $tags;
	}
	
	public static function convertHash($tag){
		$tag = str_replace(array("\n","\r"," ",","),"",$tag);
		return crc32($tag);
	}
	
	/* getter setter */

	

	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
	function getTag() {
		return $this->tag;
	}
	function setTag($tag) {
		$hash = SOYCMS_Tag::convertHash($tag);
		$this->hash = $hash;
		$this->tag = $tag;
	}
	function getHash() {
		return $this->hash;
	}
	function setHash($hash) {
		$this->hash = $hash;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
}

/**
 * @entity SOYCMS_Tag
 */
abstract class SOYCMS_TagDAO extends SOY2DAO{
	
	/**
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_Tag $bean);
	
	
	/**
	 * @trigger onDelete
	 */
	abstract function deleteByEntryId($entryId);
	
	/**
	 * @order #order#
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @distinct
	 * @columns tag_text
	 * @return row_tag
	 * @order tag_text
	 */
	abstract function getTagList();
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		return array($sql,$binds);
	}
	
	/**
	 * @final
	 */
	function onDelete($sql,$binds){
		return array($sql,$binds);
	}
	
}
