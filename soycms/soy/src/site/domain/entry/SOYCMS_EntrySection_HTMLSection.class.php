<?php

class SOYCMS_EntrySection_HTMLSection extends SOYCMS_EntrySection{
	
	function build(){
		$values = $this->getValue();
		parse_str($values,$values);
		$content = $values["CONTENT"];
		$this->setContent($content);
		$this->setValue(null);
	}

}
?>