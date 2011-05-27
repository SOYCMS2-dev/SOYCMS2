<?php

class page_entry_operation extends SOYCMS_WebPageBase{
	
	function page_entry_operation() {
		
		if($_REQUEST["mode"] == "sort"){
			$this->doSort();
		}
   		
   		
   		if($_POST["mode"] == "trash"){
   			$this->doTrash();	
   		}
   		
   		if($_POST["mode"] == "open" || $_POST["mode"] == "close"){
   			$this->doUpdatePublish(($_POST["mode"] == "open"));	
   		}
   		
   		exit;
	}
	
	/**
	 * 並び順変更
	 */
	function doSort(){
		$entryId = $_GET["id"];
		$diff = $_GET["diff"];
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$entry = $dao->getById($entryId);
		
		try{
		
			if($diff > 0){
				$target = $dao->getNextEntry($entry);
			}else{
				$target = $dao->getPrevEntry($entry);
			}
		}catch(Exception $e){
			echo "<hr />";
			echo $entry->getId();
			echo ",";
			echo $entry->getOrder();
			echo "<br />";
			echo $e->getMessage();
			exit;
		}
		
		
		$oldOrder = $entry->getOrder();
		$newOrder = $target->getOrder();
		if($oldOrder == $newOrder){
			$newOrder = ($diff > 0) ? $newOrder + 1 : $newOrder - 1;
		}
		
		$dao->updateOrder($entry->getId(),$newOrder);
		$dao->updateOrder($target->getId(),$oldOrder);
		
		exit;
	}
	
	/**
	 * ゴミ箱に投入
	 */
	function doTrash(){
		$entryIds = explode(",",$_POST["entryIds"]);
		$isAll = ($_POST["all"] == 1);
		$dir = $_POST["directory"];
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		if($isAll){
			$dao->updatePublishByDirectory(-1,$dir);
		}else{
			$dao->begin();
			foreach($entryIds as $entryId){
				$dao->updatePublishById($entryId,-1);
			}
			$dao->commit();
		}
		
		//ゴミ箱に入った記事の数
		$count_trash = $dao->countByPublishStatus(-1);
		echo $count_trash;
		exit;
	}
	
	/**
	 * 公開、非公開切りかえ
	 */
	function doUpdatePublish($flag){
		$entryIds = explode(",",$_POST["entryIds"]);
		$isAll = ($_POST["all"] == 1);
		$dir = $_POST["directory"];
		
		$value = ($flag) ? 1 : 0;
		$status = ($flag) ? "open" : "close";
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		
		if($isAll){
			$dao->updatePublishByDirectory($value,$dir);
			$dao->updateStatusByDirectory($status,$dir);
		}else{
			$dao->begin();
			foreach($entryIds as $entryId){
				$dao->updatePublishById($entryId,$value);
				$dao->updateStatusById($entryId,$status);
			}
			$dao->commit();
		}
		
		echo 1;
		exit;
	}
}
?>