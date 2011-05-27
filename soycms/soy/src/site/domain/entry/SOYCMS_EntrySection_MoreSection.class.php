<?php

class SOYCMS_EntrySection_MoreSection extends SOYCMS_EntrySection{
	
	function build(){
		$this->setContent("<p class=\"entry-more\" style='text-align:center'>-----------------------more-----------------------<p>");
	}
	
	function getSectionHeight(){
		return 0;
	}

}
?>