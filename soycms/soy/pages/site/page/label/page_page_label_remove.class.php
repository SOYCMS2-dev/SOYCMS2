<?php
SOY2HTMLFactory::importWebPage("page.label.page_page_label_detail");

class page_page_label_remove extends page_page_label_detail{
	
	function doPost(){
		
		if(soy2_check_token()){
			$this->label->delete();
		}
		
		$this->jump("/page/label?removed");
	}
	
	function page_page_label_remove($args) {
		$this->id = $args[0];
		
		WebPage::WebPage();
		
		$this->buildForm();
		$this->buildPages();
	
	}
}
?>