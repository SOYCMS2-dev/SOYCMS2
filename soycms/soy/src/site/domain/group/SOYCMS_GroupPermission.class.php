<?php
/**
 * @table soycms_group_permission
 */
class SOYCMS_GroupPermission extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
    private $id;
    
    /**
     * @column page_id
     */
    private $pageId;
    
    /**
     * @no_persistent
     */
    private $groupName;
    
    /**
     * @column group_id
     */
    private $groupId;
    
    private $readable = 1;
    
    private $writable = 1;
    
    function isReadable(){
    	return $this->getReadable();
    }
    function isWritable(){
    	return ($this->getReadable() && $this->getWritable());
    }
	
	/* getter setter */    

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getGroupId() {
    	return $this->groupId;
    }
    function setGroupId($groupId) {
    	$this->groupId = $groupId;
    }
    function getReadable() {
    	return $this->readable;
    }
    function setReadable($readable) {
    	$this->readable = (int)$readable;
    }
    function getWritable() {
    	return $this->writable;
    }
    function setWritable($writable) {
    	$this->writable = (int)$writable;
    }

    function getGroupName() {
    	return $this->groupName;
    }
    function setGroupName($groupName) {
    	$this->groupName = $groupName;
    }

    function getPageId() {
    	return $this->pageId;
    }
    function setPageId($pageId) {
    	$this->pageId = $pageId;
    }
}


/**
 * @entity SOYCMS_GroupPermission
 */
abstract class SOYCMS_GroupPermissionDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_GroupPermission $bean);

	abstract function update(SOYCMS_GroupPermission $bean);	
	
	abstract function delete($id);
	
	abstract function deleteByGroupId($groupId);
	
	abstract function deleteByPageId($pageId);
	
	abstract function getByGroupId($groupId);
	
	/**
	 * @index groupId
	 */
	abstract function getByPageId($pageId);
	
	abstract function get();
	
	/**
	 * @return object
	 * @query #pageId# = :pageId and #groupId# = :groupId
	 */
	abstract function getByParams($pageId,$groupId);
	
}