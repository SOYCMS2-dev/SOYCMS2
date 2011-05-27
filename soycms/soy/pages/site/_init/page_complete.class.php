<?php 
/**
 * @class CompletePage
 * @date 2010-04-09T03:41:48+09:00
 * @author SOY2HTMLFactory
 */ 
class page_complete extends SOYCMS_WebPageBase{
	
	private $id;
	
	function page_complete($args){
		$this->id = @$_GET["init_site"];
		
		WebPage::WebPage();
		
		$this->addLink("login_link",array(
			"link" => SOY2PageController::createRelativeLink("../admin/site/login/" . $this->id) . "?login"
		));

	}
	
	
	function getLayout(){
		return "frame.php";
	}
}


?>