<?php

class HTMLMeta extends SOY2HTML{

	const SOY_TYPE = SOY2HTML::SKIP_BODY;
	var $tag = "meta";
 	
 	function getObject(){
 		return "";
 	}   
 	
 	/**
 	 * metaの末尾に改行が入るように
 	 */
 	function getStartTag(){
 		return parent::getStartTag() . "\n";
 	}
}
?>