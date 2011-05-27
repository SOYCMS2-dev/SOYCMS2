<?php
SOY2::imports("site.domain.entry.*");
/**
 * @table soycms_site_entry
 */
class SOYCMS_EntryTemplate extends SOY2DAO_EntityBase{
	
	/**
	 * @id
	 */
	private $id;
	
	private $directory;
	
	private $name;
	
	private $title;
	
	private $description;
	
	private $sections;
	
	private $_sections;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function check(){
		if(!$this->directory)return false;
		return true;
	}
	
	function buildContent(){
		$list = $this->getSectionsList();
		$content = "";
		foreach($list as $section){
			if($section instanceof SOYCMS_EntrySection_IntroductionSection){
				$content = "";
				continue;
			}
			
			if($section instanceof SOYCMS_EntrySection_MoreSection){
				continue;
			}
			
			$content .= $section->getContent();
		}
		return $content;
	}
	
	/**
	 * 記事を作成する
	 */
	function buildEntry($entry = null){
		if(!$entry){
			$entry = new SOYCMS_Entry();
		}
		
		$title = $this->getTitle();
		
		//変数から置換
		$title = str_replace('%YYYY%',date("Y"),$title);
		$title = str_replace('%MM%',date("m"),$title);
		$title = str_replace('%DD%',date("d"),$title);
		
		//VOL
		if(strpos($title,'%VOL%') !== false){
			$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
			$vol = $dao->countByDirectory($this->getDirectory());
			$title = str_replace('%VOL%',$vol,$title);
		}
		
		$entry->setTitle($title);
		$entry->setTitleSection($title);
		$entry->setSections($this->getSections());
		
		return $entry;
	}
	
	function setContent($content){
		//dummy
	}
	
	function getSectionsList(){
		if(!$this->_sections){
			$this->_sections = SOYCMS_EntrySection::unserializeSection($this->sections);
		}
		
		return $this->_sections;
	}
	
	function setSections($sections) {
		if(!is_string($sections)){
			$this->_sections = $sections;
			$sections = SOYCMS_EntrySection::serializeSection($sections);
		}
		$this->sections = $sections;
	}
	
	function setSectionsList($sections){
		$this->_sections = $sections;
		$this->sections = SOYCMS_EntrySection::serializeSection($sections);
	}
	
	function getSections(){
		return $this->sections;
	}
	
	/* getter setter */
	


	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	
	function getCreateDate() {
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
}

/**
 * @entity SOYCMS_EntryTemplate
 */
abstract class SOYCMS_EntryTemplateDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYCMS_EntryTemplate $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCMS_EntryTemplate $bean);	
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @columns count(id) as template_count
	 * @return column_template_count
	 */
	abstract function countByDirectory($directory);
	
	/**
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @final
	 */
	function onUpdate($sql,$binds){
		$binds[":updateDate"] = time();
		return array($sql,$binds);
	}
	
	/* DBはSQLiteで各サイト毎 */
	
	/**
	 * @final
	 * @override
	 */
	function getDataSource(){
		$dsn = "sqlite:" . $this->getDBPath();
		
		if(!file_exists($this->getDBPath())){
			$this->init($dsn);
		}
		
		$pdo = SOY2DAO::_getDataSource($dsn);
		
		try{
			$this->check();
		}catch(Exception $e){
			$this->init($dsn);
		}
		
		return $pdo;	
	}
	
	/**
	 * @query id < -1
	 */
	function check(){
		$query = $this->getQuery();
		$dsn = "sqlite:" . $this->getDBPath();
		$pdo = SOY2DAO::_getDataSource($dsn);
		$pdo->exec($query);
	}
	
	/**
	 * @final
	 */
	function init($dsn){
		$pdo = SOY2DAO::_getDataSource($dsn);
		$sql = file_get_contents(SOYCMS_COMMON_DIR . "sql/site/entrytemplate.sql");
		$sqls = explode(";",$sql);

		foreach($sqls as $sql){
			try{
				if(empty($sql))continue;
				$pdo->exec($sql);
			}catch(Exception $e){
			}
		}
		 
	}
	
	/**
	 * @final
	 * @return string
	 */
	function getDBPath(){
		return SOYCMSConfigUtil::get("db_dir") . SOYCMS_LOGIN_SITE_ID . "_entrytemplate.db";
	}
	
}

?>