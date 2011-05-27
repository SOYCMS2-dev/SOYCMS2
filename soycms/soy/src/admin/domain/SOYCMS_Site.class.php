<?php
/**
 * @table soycms_site
 */
class SOYCMS_Site extends SOY2DAO_EntityBase{

	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column site_id
	 */
	private $siteId;
	
	/**
	 * @column site_name
	 */
	private $name;
	
	/**
	 * @column site_path
	 */
	private $path;
	
	/**
	 * @column site_url
	 */
	private $url;
	
	/**
	 * @column site_config
	 */
	private $config;
	private $_config;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getSiteId() {
		return $this->siteId;
	}
	function setSiteId($siteId) {
		$this->siteId = $siteId;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getPath() {
		return $this->path;
	}
	function setPath($path) {
		$this->path = $path;
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(!is_string($config)){
			$this->_config = $config;
			$config = soy2_serialize($config);
		}
		$this->config = $config;
	}
	function getConfigObject(){
		if(!$this->_config){
			$obj = soy2_unserialize($this->config);
			if($obj){
				$this->_config = $obj;
			}else{
				$this->_config = array(
					"dbtype" => "sqlite",
					"dsn" => "",
					"user" => "",
					"pass" => ""
				);
			}
		}
		
		return $this->_config;
	}
	function setConfigObject($config){
		$this->_config = $config;
		$this->config = soy2_serialize($config);
	}
	
	function check(){
		if(strlen($this->siteId)<1)return false;
		if(strlen($this->name)<1)$this->name = $this->siteId;
		if(strpos($this->path,SOYCMS_ROOT_DIR) !== false)return false;
		
		return true;
	}
	
	/**
	 * @return boolean
	 */
	public static function checkSiteId($siteId){
		$dao = SOY2DAOFactory::create("SOYCMS_SiteDAO");
		try{
			$dao->getBySiteId($siteId);
			return true;
		}catch(Exception $e){
			return false;
		}
	}
}

/**
 * @entity SOYCMS_Site
 */
abstract class SOYCMS_SiteDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYCMS_Site $bean);

	abstract function update(SOYCMS_Site $bean);	
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getBySiteId($siteId);
	
	/**
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @final
	 */
	function getDataSource(){
		return SOY2DAO::_getDataSource(SOYCMS_DB_DSN,SOYCMS_DB_USER,SOYCMS_DB_PASS);
	}
}
?>