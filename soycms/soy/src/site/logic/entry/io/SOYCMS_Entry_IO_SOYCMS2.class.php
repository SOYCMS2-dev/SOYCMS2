<?php

class SOYCMS_Entry_IO_SOYCMS2 extends SOYCMS_Entry_IOBase{

	function export(SOYCMS_Entry $entry){
		$xml = new SOYCMS_EntryXML($entry);
		return $xml->xml() . "\n";	
	}
	
	function imports($arg){
		return SOYCMS_EntryXML::toArray($arg);
	}

}
?>