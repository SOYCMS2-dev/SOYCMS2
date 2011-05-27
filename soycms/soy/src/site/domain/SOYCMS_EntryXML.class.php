<?php

class SOYCMS_EntryXML{

	/**
	 * @return string
	 */	
	public static function toXML($array){
		
		$xml = array();
		$xml[] = '<?xml version="1.0" encoding="UTF-8" ?>';
		$xml[] = "<soycms>";
		$xml[] = "<entries>";
		
		foreach($array as $obj){
			$xmlObj = new SOYCMS_EntryXML($obj);
			$xml[] = "";
			$xml[] = $xmlObj->xml();
			$xml[] = "";
		}
		
		$xml[] = "</entries>";
		$xml[] = "</soycms>";
		
		return implode("\n",$xml);
	}
	
	/**
	 * @return array
	 */
	public static function toArray($xml){
		$xml = simplexml_load_string($xml);
		$array = $xml->entry;
		
		$res = array();
		
		if(!$array){
			return $array;
		}
		
		foreach($array as $entryXML){
			$xmlObj = new SOYCMS_EntryXML($entryXML);
			$res[] = $xmlObj->entry;
		}
		
		return $res;
	}
	
	private $id;	/* 記事IDと一致 */
	private $entry;
	
	/**
	 * constructor
	 */
	function SOYCMS_EntryXML($arg){
		if(is_numeric($arg)){
			$this->id = $arg;
			$this->entry = SOY2DAO::find("SOYCMS_Entry",$arg);
		}else if($arg instanceof SOYCMS_Entry){
			$this->id = $arg->getId();
			$this->entry = $arg;
		}else if(is_object($arg)){
			$this->entry = new SOYCMS_Entry();
			foreach($arg as $key => $value){
				$method = "set" . ucfirst($key);
				if(method_exists($this->entry,$method)){
					$this->entry->$method((string)$value);
				}	
			}
		}
	}
	
	/**
	 * XMLに変換する
	 */
	function xml(){
		$array = SOY2::cast("array",$this->entry);
		
		$xml = array();
		
		$xml[] = '<entry id="'.$array["id"].'">';
			foreach($array as $key => $value){
				if($key[0] == "_")continue;
				
				if(strpos($value,">")!==false){
					$value = "\n\t<![CDATA[ \n\t" .
								$value . "\n" . 
							"\t]]>\n\t";
				}
				if(is_bool($value)){
					$value = (int)$value;
				}
				
				$xml[] = "\t<$key>$value</$key>";
			}	
				
			//attr
			$attr = SOYCMS_EntryAttribute::getByEntryId($this->entry->getId());
			
			$xml[] = "\t<attributes>";
			
			foreach($attr as $key => $obj){
				if(strpos($value,">")!==false){
					$value = "\n\t\t<![CDATA[ \n\t" .
							$value . "\n" . 
						"\t\t]]>\n\t\t";
				}
				if(is_bool($value)){
					$value = (int)$value;
				}
				
				$xml[] = "\t\t<$key>$value</$key>";
			}
			
			$xml[] = "\t</attributes>";	
		
		$xml[] = '</entry>';
		
		return implode("\n",$xml);
	}
}
?>