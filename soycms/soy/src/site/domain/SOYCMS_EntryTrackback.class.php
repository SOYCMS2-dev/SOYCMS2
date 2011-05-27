<?php
/**
 * @table soycms_entry_trackback
 */
class SOYCMS_EntryTrackback extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column entry_id
	 */
	private $entryId;
	
	private $excerpt;
	
	private $url;
	
	private $title;
	
	/**
	 * @column blog_name
	 */
	private $blogName;
	
	/**
	 * @column trackback_status
	 */
	private $status = -1;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column trackback_attributes
	 */
	private $attributes;
	private $_attributes = array();
	
	/**
	 * @read_only
	 * @column (select title from soycms_site_entry where soycms_site_entry.id = soycms_entry_trackback.entry_id)
	 */
	private $entryTitle;
	
	function setAttribute($key,$value){
		$attr = $this->getAttributeArray();
		$attr[$key] = $value;
		$this->setAttributeArray($attr);
	}
	function getAttribute($key){
		$attr = $this->getAttributeArray();
		return (isset($attr[$key])) ? $attr[$key] : null;
	}
	function getAttributeArray(){
		if(is_null($this->_attributes)){
			$this->_attributes = soy2_unserialize($this->attributes);
			if(!$this->_attributes){
				$this->setAttributeArray(array());
			}
		}
		return $this->_attributes;
	}
	function setAttributeArray($attr){
		$this->_attributes = $attr;
		$this->attributes = soy2_serialize($this->_attributes);
	}

	function check(){
		
		if(!$this->entryId)return false;
		if(!$this->title)$this->title = "[no title]";
		
		return true;
	}
	
	function getStatusText(){
		$text = array(
			-1 => "未読",
			0 => "非公開",
			1 => "公開"
		);
		
		return @$text[$this->status];
	}
	
	/* getter setter */


	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
	function getExcerpt() {
		return $this->excerpt;
	}
	function setExcerpt($excerpt) {
		$this->excerpt = $excerpt;
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getBlogName() {
		return $this->blogName;
	}
	function setBlogName($blogName) {
		$this->blogName = $blogName;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getSubmitDate() {
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}
	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	function getEntryTitle() {
		return $this->entryTitle;
	}
	function setEntryTitle($entryTitle) {
		$this->entryTitle = $entryTitle;
	}
}


/**
 * @entity SOYCMS_EntryTrackback
 */
abstract class SOYCMS_EntryTrackbackDAO extends SOY2DAO{
	
	abstract function insert(SOYCMS_EntryTrackback $bean);
	
	abstract function update(SOYCMS_EntryTrackback $bean);
	
	/**
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @order id desc
	 */
	abstract function getByStatus($status);

	/**
	 * @order id desc
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @columns count(id) as tb_count
	 * @return column_tb_count
	 */
	abstract function count();
	
	/**
	 * @columns count(id) as tb_count
	 * @return column_tb_count
	 */
	abstract function countByStatus($status);
	
	/**
	 * @columns count(id) as tb_count
	 * @return column_tb_count
	 */
	abstract function countByEntryId($entryId);
	
	/**
	 * @columns id,#status#
	 * @query id in (<?php implode(',',:ids) ?>)
	 */
	abstract function updateStatus($status,$ids);
	
	/**
	 * @final
	 */
	function deleteByIds($ids){
		$this->begin();
		foreach($ids as $id){
			$this->delete($id);
		}
		$this->commit();
	}
}
?>