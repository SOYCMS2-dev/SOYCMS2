<?php

class SOYCMS_CommentListComponent extends HTMLList{
	
	private $entry;
	
	function getStartTag(){
		return '<?php SOYCMS_ItemWrapComponent::startTag("comment_list"); ?>' .
		 			'<a name="comment_list"></a>' . parent::getStartTag() . 
		 	'<a name="comment_list_no_<?php echo $'.$this->getParentPageParam().'["comment_list"][$key]["comment_id"]; ?>"></a>';
		
	}

	
	function getEndTag(){
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
	function init(){
		if($this->entry){
			$list = SOY2DAO::find("SOYCMS_EntryComment",array("entryId" => $this->entry->getId()));
			$this->setList($list);
		}
	}

	function populateItem($entity){
		
		$this->addLabel("comment_id",array(
			"text" => $entity->getId(),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("comment_title",array(
			"text" => $entity->getTitle(),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("comment_content",array(
			"html" => nl2br(htmlspecialchars($entity->getContent())),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("comment_author",array(
			"text" => $entity->getAuthor(),
			"soy2prefix" => "cms"
		));
		
		$this->addLabel("comment_url",array(
			"title" => $entity->getUrl(),
			"soy2prefix" => "cms"
		));
		
		$this->addLink("comment_url_link",array(
			"link" => $entity->getUrl(),
			"soy2prefix" => "cms",
			"visible" => (strlen($entity->getUrl())>0)
		));
		
		$this->addLabel("comment_mail",array(
			"text" => $entity->getMail(),
			"soy2prefix" => "cms"
		));
		
		$this->addLink("comment_mail_link",array(
			"link" => (strlen($entity->getMail())>0) ? "mailto:" . $entity->getMail() : "",
			"soy2prefix" => "cms",
			"visible" => (strlen($entity->getMail())>0)
		));
		
		$this->createAdd("comment_submit_date","DateLabel",array(
			"text" => $entity->getSubmitDate(),
			"soy2prefix" => "cms"
		));
		
		//非表示系はダイナミックの時以外表示しない
		if((!defined("SOYCMS_EDIT_DYNAMIC") || SOYCMS_EDIT_DYNAMIC != true) && $entity->getStatus() < 1){
			return false;
		}
		
		
	}

	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
}
?>