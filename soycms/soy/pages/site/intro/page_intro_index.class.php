<?php

class page_intro_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		if(isset($_POST["finish_intro"]) && $_POST["finish_intro"] > 0){
			SOYCMS_DataSets::put("finish_intro",1);
		}
		
		$this->jump("");
	}

	function page_intro_index() {
		WebPage::WebPage();
		
		$this->addForm("form");
	}
}
?>