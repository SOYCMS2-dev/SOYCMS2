<?php

class page_page_history_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["remove"])){
			$this->history->delete();
			$this->jump("/page/".$this->history->getObject()."/detail?id=" . $this->history->getObjectId() . "&deleted");
		}
		
		if(isset($_POST["do_rollback"])){
			
			//Object
			$obj = $this->history->getSourceObject();
			
			//add to history(revert)
			SOYCMS_History::addHistory($this->history->getObject(),$obj,"revert");
			
			
			$obj->setContent($this->history->getContent());
			$obj->save();
			
			$this->jump("/page/".$this->history->getObject()."/detail?id=" . $this->history->getObjectId() . "&updated");
			
		}
		
	}
	
	private $id;
	private $object;
	private $history;
	
	function init(){
		$this->history = SOY2DAO::find("SOYCMS_History",$this->id);
	}

	function page_page_history_detail($args) {
		$this->id = $args[0];
		WebPage::WebPage();
		
	}
	
	function main(){
		$this->buildPage();
	}
	
	function buildPage(){
		$this->addForm("form");
		
		$this->addLabel("name",array("text" => $this->history->getName()));
		$this->addLink("detail_link",array(
			"link" => soycms_create_link("page/" . $this->history->getObject() . "/detail?id=" . $this->history->getObjectId())
		));
		
		$this->addLabel("history_title_text",array(
			"text" => $this->history->getName()
		));
		
		$this->addLabel("history_date",array(
			"text" => date("Y-m-d H:i:s",$this->history->getSubmitTime())
		));
		
		$this->addLabel("history_content",array(
			"text" => $this->history->getContent()
		));
		
		
		
		//最新を取得
		$historyDAO = SOY2DAOFactory::create("SOYCMS_HistoryDAO");
		$historyDAO->setLimit(1);
		$histories = $historyDAO->listByParams($this->history->getObject(),$this->history->getObjectId());
		
		$this->addModel("not_recent_history",array(
			"visible" => (count($histories) < 1 || $histories[0]->getId() != $this->history->getId())
		));
	}
	
	/* getter setter */

	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getObjectId() {
		return $this->objectId;
	}
	function setObjectId($objectId) {
		$this->objectId = $objectId;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
}
?>