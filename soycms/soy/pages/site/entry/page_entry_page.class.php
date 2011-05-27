<?php
/**
 * @title ページの記事へジャンプ
 */
class page_entry_page extends SOYCMS_WebPageBase{

	function page_entry_page($args){
		try{
			$pageId = $args[0];
			$page = SOY2DAO::find("SOYCMS_Page",$pageId);
			if($page->isDirectory()){
				throw new Exception("");
			}
		}catch(Exception $e){
			$this->jump("/entry");
		}
		
		$entry = SOY2DAO::find("SOYCMS_Entry",array("directory" => $pageId));
		$config = $page->getConfigObject();
		$publish = ($config["public"] == 1) ? 1 : 0;
			
		if(count($entry) != 1){
			foreach($entry as $obj){
				$obj->remove();
			}
			$entry = new SOYCMS_Entry();
			$entry->setDirectory($pageId);
			$entry->setTitle($page->getName());
			$entry->setUri("");
			if($publish){
				$entry->setStatus("open");
			}else{
				$entry->setStatus("close");
			}
			$entry->setPublish($publish);
			$entry->setOrder(0);
			$entry->save();
			
		}else{
			$entry = array_shift($entry);
			if($entry->getPublish() != $publish){
				$entry->setPublish($publish);
				$entry->setOrder(0);
				if($publish){
					$entry->setStatus("open");
				}else{
					$entry->setStatus("close");
				}
				$entry->save();
			}

		}
		
		$this->jump("/entry/detail/" . $entry->getId());
	}
	
}