<?php
/**
 * 記事のインポート、エクスポート周り
 */
class SOYCMS_EntryImportExportLogic extends SOY2LogicBase{
	
	private $id;
	private $type;
	private $page;
	private $dao;
	private $logic;
	private $options = array();
	
	function prepare(){
		$logic = "site.logic.entry.io.SOYCMS_Entry_IO_" . strtoupper($this->type);
		$class = SOY2::import($logic);
		if(!class_exists($class))$class = "SOYCMS_Entry_IOBase";
		
		$this->dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		$this->logic = SOY2Logic::createInstance($class);
		$this->logic->setId($this->id);
		$this->logic->setDirectory($this->page->getPageObject());
		$this->logic->setDao($this->dao);
	}
	
	/**
	 * オプションを設定
	 */
	function setOption($key,$value){
		$this->options[$key] = $value;
	}
	
	/**
	 * オプションを取得
	 */
	function getOption($key){
		return (isset($this->options[$key])) ? $this->options[$key] : null;
	}
	
	/**
	 * 記事のエクスポート
	 */
	function exportByDirectory(){
		
		$logic = $this->logic;
		$directory = $this->id;
		
		$dao = $this->dao;
		
		$array = $dao->listByDirectory($directory);
		
		echo $logic->getExportHeaders();
		ob_flush();
		flush();
		
		foreach($array as $entryId){
			$entry = $dao->getById($entryId);
			$res = $logic->export($entry);
			echo $res;
			ob_flush();
			flush();
		}
		
		echo $logic->getExportFooters();
		ob_flush();
		flush();
	}
	
	/**
	 * 記事のインポート
	 */
	function importToDirectory($content){
		$logic = $this->logic;
		$directory = $this->id;
		
		$dao = $this->dao;
		
		//SOYCMS_Entryの配列に変換
		$array = $logic->imports($content);
		
		$newCount = 0;
		$updateCount = 0;
		
		foreach($array as $entry){
			$arguments = null;
			
			//返り値が配列の場合は記事、その他
			if(is_array($entry)){
				$arguments = $entry;
				$entry = array_shift($arguments);	
			}
			
			//追加するか設定
			$append = $this->getOption("append");
			$draft = $this->getOption("draft"); 
			
			$isNew = false;
			
			try{
				$obj = $dao->getByUri($entry->getUri(),$directory);
				SOY2::cast($obj,$entry);
			}catch(Exception $e){
				$obj = new SOYCMS_Entry();
				SOY2::cast($obj,$entry);
				$obj->setId(null);
			}
			
			
			$obj->setDirectory($directory);
			
			
			if($append){
				$obj->setId(null);
			}
			
			if($draft && !is_null($obj->getId())){
				$obj->setPublish(0);
				$obj->setStatus("draft");
			}
			
			$isNew = is_null($obj->getId());
			
			//インポート実行
			$res = $logic->import($obj,$arguments);
			
			if($res){
				if($isNew){
					$newCount++;
				}else{
					$updateCount++;	
				}
			}
			
		}
		
		return array($newCount,$updateCount);
	}
	
	/**
	 * ヘッダーの出力
	 */
	function outputHeader(){
		$filename = str_replace(array("/","."),"_",$this->page->getUri());
		
		header("Cache-Control: public");
		header("Pragma: public");
		header("Content-Type: ". $this->logic->getContentType());
		header("Content-Disposition: attachment; filename=" . $filename . "_" . date("Ymd")."." . $this->logic->getExtension());
		error_reporting(0);
		
	}
	
	/* getter setter */

	function getOptions() {
		return $this->options;
	}
	function setOptions($options) {
		$this->options = $options;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getLogic() {
		return $this->logic;
	}
	function setLogic($logic) {
		$this->logic = $logic;
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
}

/**
 * IO周りの標準
 */
class SOYCMS_Entry_IOBase extends SOY2LogicBase{
	
	private $id;
	private $directory;
	private $dao;
	
	function getExportHeaders(){
		return '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
				"<entries>\n";
		
	}
	
	function getExportFooters(){
		return "\n</entries>";
	}
	
	function getContentType(){
		return "text/xml;";
	}
	
	function getExtension(){
		return "xml";
	}

	
	/**
	 * 出力
	 */
	function export(SOYCMS_Entry $entry){
		
	}
	
	/**
	 * オブジェクトに変換
	 * @return array(SOYCMS_Entry)
	 */
	function imports($arg){
		return array();
	}
	
	/**
	 * 個別に呼ばれる
	 * @return boolean
	 */
	function import(SOYCMS_Entry $entry,$arguments = null){
		//新規追加の時
		if(!$entry->getId()){
			$uri = $entry->getUri();
			if($this->dao->checkUri($uri,$entry->getDirectory())){
				$entry->setUri("");
				$entry->save();
				
				$entry->setUri($this->directory->getEntryUri($entry));
			}
		}
		
		$entry->save();
		
		return true;
	}
	

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}

	function getDao() {
		return $this->dao;
	}
	function setDao($dao) {
		$this->dao = $dao;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
}
?>