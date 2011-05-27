<?php
class page_entry_xml extends SOYCMS_WebPageBase{
	
	function page_entry_xml($args){
		$this->id = @$args[0];
		$this->entry = ($this->id) ? SOY2DAO::find("SOYCMS_Entry",($this->id)) : new SOYCMS_Entry;
		
		$xml =SOYCMS_EntryXML::toXML(array($this->entry,$this->entry));
		$array = SOYCMS_EntryXML::toArray($xml);
		
		exit;
	}
	

}
?>
