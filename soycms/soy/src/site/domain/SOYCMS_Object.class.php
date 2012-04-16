<?php
/**
 * @table soycms_site_object
 */
class SOYCMS_Object extends SOY2DAO_EntityBase{

	private $id;
	
	/**
	 * @column object_title
	 */
	private $title;
	
	/**
	 * @column object_content
	 */
	private $content;
	
	/**
	 * @column owner_id
	 */
	private $ownerId;
	
	private $directory = null;
	
	/**
	 * @column object_type
	 */
	private $type = null;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	
	/* getter setter */
	
}
?>