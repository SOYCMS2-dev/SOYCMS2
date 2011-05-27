<?php

class SOYCMS_EntrySection_IntroductionSection extends SOYCMS_EntrySection{

    function build(){
		$this->setContent("<p class=\"entry-more\" style='text-align:center'>-------------------introduction-------------------<p>");
	}
	
	function getSectionHeight(){
		return 15;
	}
}
?>