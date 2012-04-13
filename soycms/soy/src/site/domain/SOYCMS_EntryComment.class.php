<?php
/**
 * @table soycms_entry_comment
 */
class SOYCMS_EntryComment extends SOY2DAO_EntityBase{

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column entry_id
	 */
	private $entryId;
	
	private $title;
	private $author;
	private $mail;
	private $url;
	private $content;
	
	/**
	 * @column submit_date
	 */
	private $submitDate;
	
	/**
	 * @column comment_status
	 * unread -1
	 * close 0
	 * open 1
	 */
	private $status = -1;
	
	/**
	 * @column comment_attributes
	 */
	private $attributes;
	private $_attributes = array();
	
	/**
	 * @column comment_order
	 */
	private $order = 0;
	
	/**
	 * @read_only
	 * @column (select title from soycms_site_entry where soycms_site_entry.id = soycms_entry_comment.entry_id)
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
		if(!$this->content)return false;
		
		return true;
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
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getAuthor() {
		return $this->author;
	}
	function setAuthor($author) {
		$this->author = $author;
	}
	function getMail() {
		return $this->mail;
	}
	function setMail($mail) {
		$this->mail = $mail;
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getContent() {
		return $this->content;
	}
	function setContent($content) {
		$this->content = $content;
	}
	function getSubmitDate() {
		if(!$this->submitDate)$this->submitDate = time();
		return $this->submitDate;
	}
	function setSubmitDate($submitDate) {
		$this->submitDate = $submitDate;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}

	function getEntryTitle() {
		return $this->entryTitle;
	}
	function setEntryTitle($entryTitle) {
		$this->entryTitle = $entryTitle;
	}
	
	function getStatusText(){
		$text = array(
			-1 => "未読",
			0 => "非公開",
			1 => "公開"
		);
		
		return @$text[$this->status];
	}
}

/**
 * @entity SOYCMS_EntryComment
 */
abstract class SOYCMS_EntryCommentDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_EntryComment $bean);
	
	abstract function update(SOYCMS_EntryComment $bean);
	
	/**
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @order id desc
	 */
	abstract function getByStatus($status);
	
	abstract function delete($id);
	
	/**
	 * @order comment_order desc
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @columns count(id) as comment_count
	 * @return column_comment_count
	 */
	abstract function count();
	
	/**
	 * @columns count(id) as comment_count
	 * @return column_comment_count
	 */
	abstract function countByStatus($status);
	
	/**
	 * @columns count(id) as comment_count
	 * @return column_comment_count
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