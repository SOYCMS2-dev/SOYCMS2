<?php

class SOYCMS_ErrorPageBase extends SOYCMS_SitePageBase{
	
	private $exception = null;
	
	function SOYCMS_ErrorPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}
	
	function setArguments($args){
		if(isset($args["exception"])){
			$this->setException($args["exception"]);
		}
		parent::setArguments($args);
	}
	
	function build($args){
		
		$page = $this->getPageObject();
		$errorPage = $page->getPageObject();
		
		//header
		$headers = $errorPage->getHeaders();
		$code = $errorPage->getStatusCode();
		$header = $headers[$code];
		header("HTTP/1.0 " . $header);
		
		$this->addLabel("status_code",array(
			"text" => $header,
			"soy2prefix" => "cms"
		));
	}


	function getException() {
		return $this->exception;
	}
	function setException($exception) {
		$this->exception = $exception;
	}
}
?>