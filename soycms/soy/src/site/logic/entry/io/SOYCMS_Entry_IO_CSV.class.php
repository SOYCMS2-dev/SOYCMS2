<?php

class SOYCMS_Entry_IO_CSV extends SOYCMS_Entry_IOBase{
	
	private $dao;
	private $columns = array();
	
	function SOYCMS_Entry_IO_CSV(){
		require_once(SOYCMS_ROOT_DIR . "soy/plugin/soycms_entry_customfield/src/common.inc.php");
	}
	
	/**
	 * ヘッダーの出力
	 */
	function getExportHeaders(){
		$label = array(
			"id",
			"uri",
			"title"
		);
		
		$columns = SOYCMS_EntryCustomFieldHelper::loadConfigure($this->getId());
		foreach($columns as $key => $column){
			$this->columns[] = $column->getId();
			$label[] = $column->getLabel();	
		}
		
		return $this->convertToCSV($label);	
	}
	
	function getExportFooters(){
		return "";
	}
	
	function getContentType(){
		return "text/plain;";
	}
	
	function getExtension(){
		return "csv";
	}
	
	function convertToCSV($array){
		$tmp = array();
		
		$quote = '"';
		foreach($array as $key => $value){
			$value = addslashes($value);
			$tmp[] = $quote . $value . $quote;
		}
		
		return implode(",", $tmp) . "\n";
	}
	
	/**
	 * CSV一行を分解する
	 * @param String
     * @return Array
	 */
	function explodeLine($line){
		$quote = '"';
    	$separator = ",";

		if($separator == "tab"){
			preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:\t|\r\n|\r|\n|$)/', $line, $matches);
		}else{
			preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:,|\r\n|\n|\r|$)/', $line, $matches);
		}
		
		//最期の1つを削除
		array_pop($matches[1]);
		
		$values = array();
		foreach($matches[1] as $value){
	    	if(
	    	    $quote
	    	    OR strlen($value) >1 AND $value[0] == '"' AND $value[strlen($value)-1] = '"'
	    	    OR ( strpos($value, "\n") !== false ) 
	    	    OR ( strpos($value, "\r") !== false ) 
	    	    OR ( $separator == "tab" AND strpos($value, "\t") !== false )
	    	    OR ( $separator != "tab" AND strpos($value, ",") !== false )
			){
	    		$value = preg_replace("/^\"/","",$value);//substr($value, 1, strlen($value)-2);
	    		$value = preg_replace("/\"\$/","",$value);//substr($value, 1, strlen($value)-2);
				$value = str_replace('""', '"', $value);
	    	}

			$values[] = $value;
		}
		
    	return $values;
	}
	
	/**
	 * 改行を含むデータの場合に行を正しく認識しなおす
	 */
	function GET_CSV_LINES($lines){
		if(!is_array($lines)){
			$lines = str_replace(array("\r\n","\r"), "\n", $lines);
			$lines = explode("\n", $lines);
		}
		$csv_lines = array();
		$status = 0;
		$buffer = "";
		foreach($lines as $line){
			$buffer .= $line;
			//まずはバッファーに付け足す
			$status = ($status + substr_count($line, '"')) % 2;
			//"が閉じていれば0
			if( $status == 0 ){
				//"が閉じていれば
				$csv_lines[] = $buffer;	//バッファーの中身を移す
				$buffer = "";	//バッファーを空にする
			}
		}
		return $csv_lines;
	}
	
	
	function getEntryValues($id){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("SOYCMS_EntryCustomField_ValueDAO");
		}
		try{
			return $this->dao->getByEntryId($id);
		}catch(Exception $e){
			return "";
		}
	}

	function export(SOYCMS_Entry $entry){
		$array = array(
			$entry->getId(),
			$entry->getUri(),
			$entry->getTitle()
		);
		
		$values = $this->getEntryValues($entry->getId());
		$columns = $this->columns;
		foreach($columns as $key){
			$array[] = (isset($values[$key])) ? $values[$key] : "";
		}
		
		return $this->convertToCSV($array);	
	}
	
	//インポート
	function imports($arg){
		$entryDAO = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		$lines = $lines = $this->GET_CSV_LINES($arg);
		
		//1行目はラベル
		array_shift($lines);
		
		$res = array();
    	foreach($lines as $line){
	    	$array = $this->explodeLine($line);
	    	try{
		    	$id = array_shift($array);
		    	$uri = array_shift($array);
		    	$title = array_shift($array);
		    	$entry = $entryDAO->getById($id);
	    	}catch(Exception $e){
	    		$entry = new SOYCMS_Entry();
	    	}
	    	
	    	$entry->setDirectory($this->getId());
	    	$entry->setUri($uri);
	    	$entry->setTitle($title);
	    	$entry->setTitleSection($title);
	    	
	    	$tmp = array($entry);
	    	$tmp = array_merge($tmp,$array);
	    	
	    	$res[] = $tmp;
    	}
    	
    	return $res;
	}
	
	function import(SOYCMS_Entry $entry,$arguments){
		if(empty($this->columns)){
			$columns = SOYCMS_EntryCustomFieldHelper::loadConfigure($this->getId());
			foreach($columns as $column){
				$this->columns[] = $column->getId();
			}
		}
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("SOYCMS_EntryCustomField_ValueDAO");
		}
		
		parent::import($entry,$arguments);
		
		/* import to custom field */
		
		$entryId = $entry->getId();
		$columns = $this->columns;
		
		if(count($columns) == count($arguments)){
			$arguments = array_combine($columns,$arguments);
			
			$values = $this->getEntryValues($entryId);
				
			$this->dao->begin();
			
			//更新分
			foreach($values as $key => $value){
				if(isset($arguments[$key])){
					$values[$key]->setValue($arguments[$key]);
					$this->dao->update($values[$key]);
					unset($arguments[$key]);
				}
			}
			
			//新規作成
			foreach($arguments as $key => $value){
				$valueObj = new SOYCMS_EntryCustomField_Value();
				$valueObj->setEntryId($entryId);
				$valueObj->setColumnId($key);
				$valueObj->setValue($value);
				$this->dao->insert($valueObj);
			}
			$this->dao->commit();
			
		}else{
			return false;
		}
		
		return true;
	}

}
?>