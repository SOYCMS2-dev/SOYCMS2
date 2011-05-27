<?php 
/**
 * @class IndexPage
 * @date 2010-07-25T16:18:40+09:00
 * @author SOY2HTMLFactory
 */ 
class IndexPage extends WebPage{
	
	function IndexPage(){
		WebPage::WebPage();
		
		$this->addModel("manager_fr",array(
			"src" => SOY2PageController::createLink("FileManager")  
		));
	}
	
}


?>