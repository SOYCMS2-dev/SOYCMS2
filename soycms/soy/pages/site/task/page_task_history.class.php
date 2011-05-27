<?php
class page_task_history extends SOYCMS_WebPageBase{
	
	private $limit = 20;
	
	function doPost(){
		
		if($taskId){
			$task = SOY2DAO::find("SOYCMS_Task",$taskId);
			
			$task->save();
			
			//sync
			$root = $task->getRoot();
			$dao = SOY2DAOFactory::create("SOYCMS_TaskDAO");
			$dao->syncStatus($task);
		}
		
		$this->jump("/task");
		
	}
	
	function page_task_history(){
		WebPage::WebPage();	
	
	}
	
	function main(){
		$this->buildPages();
	}
	
	function buildPages(){
		$this->buildTaskList();
	}
	
	function buildTaskList(){
		$dao = SOY2DAOFactory::create("SOYCMS_TaskDAO");
		$dao->setOrder("close_date desc");
		$dao->setLimit($this->limit);
		$total = $dao->countFinishTask();
		$page = (@$_GET["page"]) ? $_GET["page"] : 1;
		$dao->setOffset(($page - 1) * $this->limit);
		$list = $dao->getFinishTask();
		
		$this->createAdd("task_list","TaskList",array(
			"list" => $list
		));
		
		//pager
		$this->addPager("pager",array(
			"start" => ($page - 1) * $this->limit + 1,
			"page" => $page,
			"total" => $total,
			"limit" => $this->limit,
			"link" => soycms_create_link("/task/history?page=")
		));
	}
	
	function getLayout(){
		return ($this->_soy2_parent) ? "blank.php" : "default.php"; 
	}
	
}

class TaskList extends HTMLList {
	
	private $closeDate;
	
	function init(){

	}
	
	function populateItem($entity,$key){
		if(!$entity instanceof SOYCMS_Task)$entity = new SOYCMS_Task();
		
		$closeDate = date("Ymd",$entity->getCloseDate());
		
		$this->addLabel("close_date",array(
			"text" => date("Y年 n月 j日 (D)",$entity->getCloseDate())
		));
		
		$this->addModel("close_date_wrap",array(
			"visible" => ($closeDate != $this->closeDate)
		));
		
		$this->addLabel("task_title",array("text"=>
			(strlen($entity->getTitle())>0)?$entity->getTitle():"　　　　　　　"
		));
		
		$this->closeDate = $closeDate;;
		
		
	}
}
?>
