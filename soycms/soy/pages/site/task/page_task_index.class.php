<?php
class page_task_index extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["NewTask"]) && isset($_POST["add"])){
			$task = SOY2::cast("SOYCMS_Task",$_POST["NewTask"]);
			$task->setOwner(SOYCMS_LOGIN_USER_ID);
			$task->save();
		}
		
		if(isset($_POST["ChildTask"]) && isset($_POST["add_child"])){
			try{
				$task = SOY2::cast("SOYCMS_Task",$_POST["ChildTask"]);
				$task->setOwner(SOYCMS_LOGIN_USER_ID);
				
				//
				$parent = SOY2DAO::find("SOYCMS_Task",$task->getParent());
				$root = ($parent->getParent()) ? $parent->getRoot() : $parent->getId();
				$task->setRoot($root);
				$task->setDepth($parent->getDepth() + 1);
				
				$task->save();
			}catch(Exception $e){
				
			}
		}
		
		if(isset($_POST["Save"]) && is_array($_POST["Save"]) && count($_POST["Save"])>0){
			$key = array_shift(array_keys($_POST["Save"]));
			$task = SOY2DAO::find("SOYCMS_Task",$key);
			SOY2::cast($task,$_POST["Task"][$key]);
			$task->setId($key);
			
			$task->save();
			
			//sync
			$root = $task->getRoot();
			$dao = SOY2DAOFactory::create("SOYCMS_TaskDAO");
			$dao->syncStatus($task);
		}
		
		if(isset($_POST["save_all"])){
			foreach($_POST["Task"] as $key => $array){
				$task = SOY2DAO::find("SOYCMS_Task",$key);
				SOY2::cast($task,$array);
				$task->setId($key);
			
				$task->save();
			
				//sync
				$root = $task->getRoot();
				$dao = SOY2DAOFactory::create("SOYCMS_TaskDAO");
				$dao->syncStatus($task);
			}
		}
		
		if(isset($_POST["Remove"]) && is_array($_POST["Remove"]) && count($_POST["Remove"])>0){
			$key = array_shift(array_keys($_POST["Remove"]));
			$task = SOY2DAO::find("SOYCMS_Task",$key);
			$task->delete();
		}
		
		$this->jump("/task");
		
	}
	
	function page_task_index(){
		
		if(isset($_GET["clear"])){
			SOY2DAOFactory::create("SOYCMS_TaskDAO")->hideCompleteTask();
			$this->jump("/task");
		}
		
		WebPage::WebPage();	
	
	}
	
	function main(){
		$this->buildPages();
	}
	
	function buildPages(){
		$this->addForm("task_form",array("action"=>soycms_create_link("/task")));
		$this->buildAddForm();
		$this->buildTaskList();
	}
	
	function buildTaskList(){
		$list = SOY2DAO::find("SOYCMS_Task",array("status"=>1));
		$this->createAdd("task_list","TaskList",array(
			"list" => $list
		));
	}
	
	function buildAddForm(){
		$task = new SOYCMS_Task();
		
		$this->addInput("add_task_title",array(
			"name" => "NewTask[title]",
			"value" => $task->getTitle()
		));
		
		$this->addInput("add_task_from",array(
			"name" => "NewTask[start]",
			"value" => $task->getStart()
		));
		
		$this->addInput("add_task_to",array(
			"name" => "NewTask[end]",
			"value" => $task->getEnd()
		));
		
	}
	
	function getLayout(){
		return ($this->_soy2_parent) ? "blank.php" : "default.php"; 
	}
	
}

class TaskList extends HTMLTree {
	
	function init(){
		$list = $this->list;
		$tree = array();
		$index = array();		//子->親のマッピング
		
		foreach($list as $id => $task){
			$id = $task->getId();
			$pid = $task->getParent();
			
			if(!isset($index[$id])){
				$index[$id] = array();
			}
			
			if(is_null($pid)){
				$tree[$id] = &$index[$id];
			}else{
				$index[$pid][$id] = &$index[$id];
			}
		}
		$this->tree = $tree;
	}
	
	function populateItem($entity,$key,$depth){
		if(!$entity instanceof SOYCMS_Task)$entity = new SOYCMS_Task();
		
		$id = $entity->getId();
		$class = "task-list task-" . $depth;
		if($entity->getParent())$class .= " parent-" . $entity->getParent();
		
		$this->addLabel("task_title",array("text"=>
			(strlen($entity->getTitle())>0)?$entity->getTitle():"　　　　　　　"
		));
		
		$this->addModel("task_list",array(
			"class" => ($entity->getStatus() == 1) ? "task_complete $class" : "$class"
		));
		
		$this->addTextArea("task_title_edit",array(
			"name" => "Task[$id][title]",
			"value" => $entity->getTitle()
		));
		
		$this->addLabel("task_option_text",array(
			"text" => date("Y-m-d H:i:s",$entity->getSubmitDate())
			 . (($entity->getCloseDate()) ? " " . date("Y-m-d H:i:s",$entity->getCloseDate()) . " update!" : "")
		));
		
		$this->addInput("save_btn",array(
			"name" => "Save[$id]",
			"value" => "保存"
		));
		
		$this->addModel("add_child_btn",array(
			"onclick" => '$("#parent_task_id").val('.$entity->getId().');$(this).parents("tr").after(' .
							'$("#add_task_child").attr("class","").addClass("task-'.($depth+1).'").show());',
			"visible" => ($depth < 6)
		));
		
		$this->addCheckbox("task_check",array(
			"name" => "Task[$id][status]",
			"value" => 1,
			"selected" => ($entity->getStatus() == 1),
			"isBoolean" => true,
			"attr:task_id" => $id
		));
		
	}
}
?>
