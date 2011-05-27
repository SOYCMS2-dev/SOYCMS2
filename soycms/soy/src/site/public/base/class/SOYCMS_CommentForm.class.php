<?php

class SOYCMS_CommentForm extends SOYBodyComponentBase{
	
	private $comment;
	
	function getStartTag(){
		return '<?php SOYCMS_ItemWrapComponent::startTag("comment_form"); ?>' .
		 			parent::getStartTag() . '<a name="comment_form"></a>';
		
	}

	
	function getEndTag(){
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
	function init(){
		if(!$this->comment){
			$this->comment = new SOYCMS_EntryComment();
		}
	}

	function execute(){
		
		$array = array();
		
		$this->createAdd("comment_form","HTMLModel",array(
			"soy2prefix" => "cms",
			"method" => "post",
			"action" => str_replace("?","",@$_SERVER["REQUEST_URI"]) . "?comment",
		));
		
		$this->createAdd("comment_form_title","HTMLInput",array(
			"name" => "title",
			"value" => $this->comment->getTitle(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("comment_form_author","HTMLInput",array(
			"name" => "author",
			"value" => (strlen($this->comment->getAuthor()) > 0) ? $this->comment->getAuthor() : @$array["author"],
			"soy2prefix" => "cms"
		));

		$this->createAdd("comment_form_content","HTMLTextArea",array(
			"name" => "content",
			"value" => $this->comment->getContent(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("comment_form_mail_address","HTMLInput",array(
			"name" => "mail",
			"value" => (strlen($this->comment->getMail()) > 0) ? $this->comment->getMail() : @$array["mailaddress"],
			"soy2prefix" => "cms"
		));

		$this->createAdd("comment_form_url","HTMLInput",array(
			"name" => "url",
			"value" => (strlen($this->comment->getUrl()) > 0) ? $this->comment->getUrl() : @$array["url"],
			"soy2prefix" => "cms"
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