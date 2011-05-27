<?php

class EntryCommentForm extends HTMLForm{
	
	private $comment;
	
	function init(){
		if(!$this->comment){
			$this->comment = new SOYCMS_EntryComment();
			$session = SOY2Session::get("base.session.UserLoginSession");
			if($session)$this->comment->setAuthor($session->getName());
		}
	}
	
	function execute(){
		$array = (isset($_POST["EntryComment"])) ? $_POST["EntryComment"] : array();
		
		$this->createAdd("comment_form_title","HTMLInput",array(
			"name" => "EntryComment[title]",
			"value" => $this->comment->getTitle(),
		));

		$this->createAdd("comment_form_author","HTMLInput",array(
			"name" => "EntryComment[author]",
			"value" => (strlen($this->comment->getAuthor()) > 0) ? $this->comment->getAuthor() : "",
		));

		$this->createAdd("comment_form_content","HTMLTextArea",array(
			"name" => "EntryComment[content]",
			"value" => $this->comment->getContent(),
		));

		$this->createAdd("comment_form_mail_address","HTMLInput",array(
			"name" => "EntryComment[mail]",
			"value" => (strlen($this->comment->getMail()) > 0) ? $this->comment->getMail() : @$array["mailaddress"],
		));

		$this->createAdd("comment_form_url","HTMLInput",array(
			"name" => "EntryComment[url]",
			"value" => (strlen($this->comment->getUrl()) > 0) ? $this->comment->getUrl() : @$array["url"],
		));
		
		parent::execute();
	}
	
	
	function getComment() {
		return $this->comment;
	}
	function setComment($comment) {
		$this->comment = $comment;
	}
	
	
}
?>