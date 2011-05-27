<?php

class SOYCMS_TrackbackListComponent extends HTMLList{
	
	private $entry;
	
	function getStartTag(){
		return '<?php SOYCMS_ItemWrapComponent::startTag("trackback_list"); ?>' .
		 			parent::getStartTag() . '<a name="comment_form"></a>';
		
	}

	
	function getEndTag(){
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
	function init(){
		$list = SOY2DAO::find("SOYCMS_EntryTrackback",array("entryId" => $this->entry->getId()));
		$this->setList($list);	
		
		$this->_soy2_parent->addModel("trackback_exists",array(
			"visible"=>count($list)>0,
			"soy2prefix" => "cms"
		));
	}

	function populateItem($trackback){
		
		$this->addLabel("trackback_title",array(
			"text"=>$trackback->getTitle(),
			"soy2prefix" => "cms"
		));
		$this->addLink("trackback_url",array(
			"link"=>$trackback->getUrl(),
			"soy2prefix" => "cms"
		));
		$this->addLabel("trackback_blog_name",array(
			"text"=>$trackback->getBlogName(),
			"soy2prefix" => "cms"
		));
		$this->addLabel("trackback_excerpt",array(
			"text"=>$trackback->getExcerpt(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("trackback_submit_date","DateLabel",array(
			"text"=>$trackback->getSubmitdate(),
			"soy2prefix" => "cms"
		));
		
		//非表示系はダイナミックの時以外表示しない
		if((!defined("SOYCMS_EDIT_DYNAMIC") || SOYCMS_EDIT_DYNAMIC != true) && $trackback->getStatus() < 1){
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