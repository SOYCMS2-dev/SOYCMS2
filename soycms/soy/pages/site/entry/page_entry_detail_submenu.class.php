<?php
/**
 * @title 記事編集Subemenu
 */
class page_entry_detail_submenu extends HTMLPage{
	
	private $id;
	private $entry;
	private $user;
	private $className;
	private $mode = "edit";

	function page_entry_detail_submenu($args){
		$this->id = @$args[0];
		$this->entry = @$args[1];
		if(count($args)>2)$this->mode = $args[2];
		
		HTMLPage::HTMLPage();
		
		$this->buildPage();
	}
	
	function buildPage(){
		$this->buildOpenStatus();
		$this->buildOpenPeriod();
		$this->buildMemo();
		$this->buildTags();
		$this->buildLabels();
		$this->buildHistory();
		$this->buildMeta();
		$this->buildCommentAndTrackback();
		$this->buildEntryTemplate();
		$this->buildTrashMenu();
		
		$this->addModel("mode_edit",array(
			"visible" => $this->mode == "edit"
		));
		$this->addModel("mode_comment",array(
			"visible" => $this->mode == "comment"
		));
		$this->addModel("mode_sections",array(
			"visible" => $this->mode == "sections"
		));
		$this->addLink("entry_detail_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->id)
		));
		$this->addLink("entry_copy_link",array(
			"link" => soycms_create_link("/entry/copy/" . $this->id)
		));
		
		$this->addModel("is_entry",array(
			"visible" => (strlen($this->entry->getUri())>0)
		));
		
		$this->addLink("mode_sections_link",array(
			"link" => soycms_create_link("/entry/sections/" . $this->id),
			"visible" => $this->mode != "sections"
		));
		$this->addLink("mode_detail_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->id),
			"visible" => $this->mode == "sections"
		));
	}
	
	function buildOpenStatus(){
		
		$session = SOY2Session::get("site.session.SiteLoginSession");
		$roles = $session->getRoles();
		
		
		$this->addLink("preview_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->id . "?preview")
		));
		
		$this->addLink("check_link",array(
			"link" => soycms_create_link("/entry/detail/" . $this->id . "?check")
		));
		
		$this->addModel("is_not_public",array(
			"visible" => ($this->entry->getPublish() < 1)
		));
		
		$this->addModel("public_btn",array(
			"attr:name" => "do_open",
			"visible" => (in_array("publisher",$roles))
		));
		
		$this->addModel("is_public",array(
			"visible" => ($this->entry->getStatus() == "open")
		));
		
		$this->addModel("sync_btn",array(
			"attr:name" => "do_open",
			"visible" => (in_array("publisher",$roles))
		));
		
		$this->createAdd("update_date","_class.component.SimpleDateLabel",array(
			"date" => $this->entry->getUpdateDate()
		));
		
		$this->addLabel("create_date_text",array(
			"text" => date("Y-m-d H:i:s",$this->entry->getCreateDate())
		));
		
		$this->createAdd("create_date","_class.component.HTMLDateInput",array(
			"name" => "create_date",
			"value" => $this->entry->getCreateDate()
		));
		
		$author = (strlen($this->entry->getAuthor()) > 0)
					 ? $this->entry->getAuthor()
					 : $this->getAuthorName($this->entry->getAuthorId());
		
		$this->addLabel("author_text",array(
			"text" => $author
		));
		
		$this->addInput("author",array(
			"value" => $author
		));
		
		$this->addCheckbox("entry_author_check",array(
			"elementId" => "entry_author_check",
			"selected" => (strlen($this->entry->getAuthor()) > 0)
		));
		
		list($author_link_text,$author_link_url) = $this->getAuthorLink($this->entry->getAuthorId());
		
		$this->addInput("author_link_text",array(
			"value" => $author_link_text
		));
		$this->addInput("author_link_url",array(
			"value" => $author_link_url
		));
		
		
		//ワークフローマネージャ
		$workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		$workflow->load(); 
		
		$this->addLabel("status_text",array(
			"text" => $workflow->getStatusText($this->entry->getStatus())
		));
		
		//アクション
		$this->addForm("status_update_form");
		$actions = $workflow->getActions($this->entry->getStatus(),$roles);
		$this->createAdd("action_list","SOYCMS_ActionList",array(
			"list" => $actions,
			"memo" => $this->entry->getMemo()
		));
		
		//自動保存のチェック
		
		$this->addCheckbox("autosave",array(
			"elementId" => "autosave",
			"value" => 1,
			"selected" => SOYCMS_EntryAttribute::get($this->entry->getId(),"autosave",
				SOYCMS_DataSets::get("is_use_autosave",1)
			),
		));
	}
	
	function buildOpenPeriod(){
		
		$from = $this->entry->getOpenFrom(true);
		$to = $this->entry->getOpenUntil(true);
		
		$this->addLabel("openperiod_text",array(
			"text" => $this->entry->getOpenPeriodText() 
		));
		
		$this->addDateInput("openperiod_from",array(
			"name" => "OpenPeriod[from]",
			"value" => $from
		));
		$this->addDateInput("openperiod_until",array(
			"name" => "OpenPeriod[until]",
			"value" => $to
		));
		
		
	}
	
	function buildMemo(){
		$this->addTextArea("entry_memo",array(
			"attr:id" => "entry_memo",
			"name" => "entry_memo",
			"value" => $this->entry->getMemo()
		));
	}
	
	function buildTags(){
		$tags = SOYCMS_Tag::getByEntryId($this->entry->getId());
		$this->addList("tag_list",array(
			'populateItem:function($tag)' => '$this->addLabel("tag_text",array("text"=>$tag));',
			"list" => $tags 
		));
		
		$tag_text = implode(" ",$tags);
		
		$this->addTextArea("entry_tags",array(
			"attr:id" => "entry_tags",
			"name" => "entry_tags",
			"value" => $tag_text
		));
	}
	
	function buildLabels(){
		$mappings = SOYCMS_DataSets::get("site.page_tree_path",array());
		$dirs = @$mappings[$this->entry->getDirectory()];
		if(!$dirs)$dirs = array();
		
		$common = array();
		$directoryLabels = array();
		
		$labels = SOY2DAO::find("SOYCMS_Label");
		
		foreach($labels as $label){
			if($label->isCommon()){
				$common[$label->getId()] = $label;
			}
			if(in_array($label->getDirectory(),$dirs)){
				$directoryLabels[$label->getId()] = $label;
			}
		}
		
		$this->createAdd("label_list","EntryLabelList",array(
			"selected" => $this->entry->getLabels(),	
			"list" => $common,
		));
		
		$this->createAdd("directory_label_list","EntryLabelList",array(
			"selected" => $this->entry->getLabels(),
			"list" => $directoryLabels	
		));
	}
	
	function buildHistory($limit = 5,$current = -1){
		$historyDAO = SOY2DAOFactory::create("SOYCMS_EntryHistoryDAO");
		$historyDAO->setLimit($limit);
		try{
			$histories = $historyDAO->listByEntryId($this->entry->getId());
		}catch(Exception $e){
			$histories = array();
		}
		$this->addList("history_list",array(
			'populateItem:function($history)' => '$this->addLabel("histroy_save_date",array("html"=>($history->getId()=='.$current.')?"<strong>{$history}</strong>":$history));' .
					'$this->addLink("history_link",array("link" => "'.soycms_create_link("/entry/history/detail/").'" . $history->getEntryId() . "/" . $history->getId()));',
			"list" => $histories 
		));
		
		try{
			$count = $historyDAO->countHistoryByEntryId($this->entry->getId());
		}catch(Exception $e){
			$count = 0;
		}
		$this->addLabel("history_count",array("text"=>$count));
		
		$this->addLabel("last_histroy_text",array(
			"text" => (count($histories)>0) ? array_shift($histories) : "--"
		));	
	}
	
	function buildMeta(){
		$keyword = SOYCMS_EntryAttribute::get($this->entry->getId(),"keyword","");
		$description = SOYCMS_EntryAttribute::get($this->entry->getId(),"description","");
		
		$this->addInput("entry_keyword",array(
			"name" => "entry_keyword",
			"value" => $keyword,
		));	
		
		$this->addTextArea("entry_description",array(
			"name" => "entry_description",
			"value" => $description,
		));	
	}
	
	function buildCommentAndTrackback(){
		
		$this->addCheckbox("allow_comment",array(
			"elementId" => "allow_comment",
			"name" => "allow_comment",
			"value" => 1,
			"selected" => $this->entry->getAllowComment(),
			"onclick" => "save_comment_trackback(this);"
		));
		
		$this->addCheckbox("allow_trackback",array(
			"elementId" => "allow_trackback",
			"name" => "allow_trackback",
			"value" => 1,
			"selected" => $this->entry->getAllowTrackback(),
			"onclick" => "save_comment_trackback(this);"
		));
		
		$this->addCheckbox("send_ping",array(
			"elementId" => "send_ping",
			"name" => "send_ping",
			"value" => 1,
			"selected" => $this->entry->getAttribute("send_ping"),
			"onclick" => "save_comment_trackback(this);"
		));
		
		$this->addCheckbox("feed_entry",array(
			"elementId" => "feed_entry",
			"name" => "feed_entry",
			"value" => 1,
			"selected" => $this->entry->getIsFeed(),
			"onclick" => "save_comment_trackback(this);"
		));
		
		$this->addTextArea("trackback_destination",array(
			"attr:id" => "trackback_destination",
			"value" => $this->entry->getAttribute("trackback_destination"),
		));
		
		$this->addLink("comment_link",array(
			"link" => soycms_create_link("/entry/comment/list/" . $this->entry->getId())
		));
		$this->addLink("trackback_link",array(
			"link" => soycms_create_link("/entry/trackback/list/" . $this->entry->getId())
		));
		
		$this->addLabel("comment_count",array(
			"text" => $this->getCommentCount()
		));
		$this->addLabel("trackback_count",array(
			"text" => $this->getTrackbackCount()
		));
	}
	
	function buildEntryTemplate(){
		$this->addModel("entry_template_wrap",array(
			"visible" => (strlen($this->entry->getUri())>0)	//記事の場合のみ 
		));
		
		$this->addLink("template_create_link",array(
			"link" => soycms_create_link("/entry/template/create/" . $this->entry->getId())
		));
	}
	
	function getCommentCount(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryCommentDAO");
		return $dao->countByEntryId($this->id);
	}
	function getTrackbackCount(){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryTrackbackDAO");
		return $dao->countByEntryId($this->id);
	}
	
	function getAuthorName($id){
		try{
			$this->user = SOY2DAO::find("SOYCMS_User",$id);
			return $this->user->getName();
		}catch(Exception $e){
			return "author-" . $id;
		}
	}
	
	function getAuthorLink($id){
		//個別に設定している場合
		$text = $this->entry->getAttribute("author_link_text");
		$link = $this->entry->getAttribute("author_link_url");
		
		if(strlen($text)>0 && strlen($link)>0){
			return array($text,$link);
		}
		
		if(!$this->user){
			try{
				$this->user = SOY2DAO::find("SOYCMS_User",$id);
			}catch(Exception $e){
				return "author-" . $id;
			}
		}
		
		if($this->user){
			$config = $this->user->getConfigArray();
			return array(
				@$config["link_text"],
				@$config["link_url"]
			);
		}
		
		return array("","");
	}
	
	function buildTrashMenu(){
		$this->addForm("op_form");
		
		$this->addModel("op_trash",array(
			"visible" => $this->entry->getPublish() >= 0
		));
		
		$this->addModel("op_recover",array(
			"visible" => $this->entry->getPublish() < 0
		));
		$this->addModel("op_delete",array(
			"visible" => $this->entry->getPublish() < 0
		));
		
	}
	
	
	
	/* getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getEntry() {
		return $this->entry;
	}
	function setEntry($entry) {
		$this->entry = $entry;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}

/**
 * ラベル
 */
class EntryLabelList extends HTMLList{
	
	private $selected = array();
	
	function populateItem($entity){
		$labelId = $entity->getId();
		
		$this->createAdd("label_check","HTMLCheckbox",array(
			"elementId" => "label_" . $labelId,
			"name" => "labels[".$labelId."]",
			"value" => $labelId,
			"selected" => (isset($this->selected[$labelId])),
		));
		
		$config = $entity->getConfigObject();
		
		$this->addLabel("label_name",array(
			"attr:for" => "label_" . $labelId,
			"attr:class" => "label_" . $labelId,
			"style" => "color:" . @$config["color"] . ";background-color:" . @$config["bgcolor"],
			"text" => $entity->getName()
		));
		
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}

class SOYCMS_ActionList extends HTMLList{
	
	private $memo;
	
	function populateItem($entity,$key){
		$isMsg = ($entity->hasOperation("send_comment")) ? "true" : "false";
		$message = $entity->getOption("send_comment_msg");
		
		$this->addCheckbox("action_check",array(
			"name" => "action",
			"label" => $entity->getName(),
			"value" => $entity->getId(),
			"onclick" => '$("#message_edit").toggle('.$isMsg.');' .
					'$("#message_edit textarea").val("'.htmlspecialchars($message,ENT_QUOTES).'");' .
					'$("#change_status_btn").val("'.$entity->getName().'").show();'
		));
	}
	
	function setMemo($memo){
		$this->memo = $memo;
	}
	
}
?>