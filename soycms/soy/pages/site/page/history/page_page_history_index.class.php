<?php

class page_page_history_index extends SOYCMS_WebPageBase{
	
	private $name;
	private $objectId;
	private $type;
	private $page = 1;
	private $limit = 5;
	
	function init(){
		if(isset($_GET["type"]))$this->type = $_GET["type"];
		if(isset($_GET["page"]))$this->page = $_GET["page"];
		if(isset($_GET["objectId"]))$this->objectId = $_GET["objectId"];
		
		if(isset($_GET["type"])){
			if(strpos($this->objectId, "!") !== false){
				list($this->objectId,$optionId) = explode("!",$this->objectId);
			}
			
			$class = "SOYCMS_" . ucfirst($this->type);
			if(!class_exists($class))exit;
			eval('$obj='."$class::load('".$this->objectId."');");
			$this->name = $obj->getName();
			
			if($optionId)$this->objectId .= "!" . $optionId;
		}
		
		
	}

	function page_page_history_index() {
		WebPage::WebPage();
	}
	
	function main(){
		$this->buildPage();
	}
	
	function buildPage(){
		$dao = SOY2DAOFactory::create("SOYCMS_HistoryDAO");
		$dao->setLimit($this->limit);
		$dao->setOffset($this->limit * ($this->page - 1));
		$histories = $dao->listByParams($this->type,$this->objectId);
		try{
			$total = $dao->countByParams($this->type,$this->objectId);
		}catch(Exception $e){
			$total = 0;
		}
		
		$this->addLabel("name",array("text" => $this->name));
		$this->addModel("history_exists",array("visible"=>count($histories)>0));
		
		$this->createAdd("history_list","_class.list.HistoryList",array(
			"list" => $histories
		));
		
		//build pager
		$this->addPager("pager",array(
			"start" => ($this->page - 1) * $this->limit + 1,
			"page" => $this->page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/page/history/?type=".$this->type."&objectId=".$this->objectId."&page=")
		));
		
		$this->addLink("back_link",array(
			"link" => soycms_create_link("/page/" . $this->type . "/detail?id=" . $this->objectId)
		));
		$this->addModel("mode_page",array(
			"visible" => (isset($_GET["type"]) || isset($_GET["page"]))
		));
		
	}
	
	function getLayout(){
		if(isset($_GET["type"]) || isset($_GET["page"])){
			return parent::getLayout();
		}
		return "blank";
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