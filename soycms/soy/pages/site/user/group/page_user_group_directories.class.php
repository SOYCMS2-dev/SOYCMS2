<?php

/**
 * グループ一括設定
 */
class page_user_group_directories extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["GroupPermission"])){
			$dao = SOY2DAOFactory::create("SOYCMS_GroupPermissionDAO");
			$dao->begin();
			foreach($_POST["GroupPermission"] as $pageId => $perm){
				$dao->deleteByPageId($pageId);
				foreach($perm as $groupId => $array){
					$obj = new SOYCMS_GroupPermission();
					$obj->setGroupId($groupId);
					$obj->setPageId($pageId);
					$obj->setReadable($array["readable"]);
					$obj->setWritable($array["writable"]);
					$dao->insert($obj);
				}
			}
			$dao->commit();
		}
		
		$this->jump("/user/group/directories?updated");
	}

	function page_user_group_directories() {
		WebPage::WebPage();
		
		$this->addForm("form");
		$this->buildPages();
	}
	
	function buildPages(){
		
		$groups = SOY2DAO::find("SOYCMS_Group");
		$this->createAdd("group_list","_class.list.GroupList",array(
			"list" => $groups
		));
		
		$pages = SOYCMS_DataSets::get("site.page_mapping");
		
		$this->createAdd("page_list","page_user_group_directories_PageList",array(
			"list" => $pages,
			"groups" => $groups
		));	
		
	}
	
}

class page_user_group_directories_PageList extends HTMLList{
	
	private $detailLink;
	private $groups;
	
	function init(){
		$this->detailLink = soycms_create_link("/page/detail/group");
	}
	
	function populateItem($entity){
		$this->addLabel("page_name",array("text" => $entity["name"]));
		$this->addLink("page_detail_link",array(
			"link" => $this->detailLink . "/" . $entity["id"]
		));
		
		$this->createAdd("permission_list","page_user_group_directories_PermissionList",array(
			"list" => $this->groups,
			"pageId" => $entity["id"]
		));
		
		if($entity["type"] != "detail")return false;
	}
	

	function getGroups() {
		return $this->groups;
	}
	function setGroups($groups) {
		$this->groups = $groups;
	}
}

class page_user_group_directories_PermissionList extends HTMLList{
	
	private $pageId;
	
	function populateItem($entity){
		
		$permission = $this->getPermission($entity->getGroupId());
		
		$this->addCheckbox("is_readable",array(
			"name" => "GroupPermission[".$this->pageId."][".$entity->getGroupId()."][readable]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (!$permission || $permission->isReadable())
		));
		
		$this->addCheckbox("is_writable",array(
			"name" => "GroupPermission[".$this->pageId."][".$entity->getGroupId()."][writable]",
			"value" => 1,
			"isBoolean" => true,
			"selected" => (!$permission || $permission->isWritable())
		));
		
	}
	
	function getPermission($groupId){
		static $_dao;
		if(!$_dao)$_dao = SOY2DAOFactory::create("SOYCMS_GroupPermissionDAO");
		
		try{
			$perm = $_dao->getByParams($this->pageId,$groupId);
		}catch(Exception $e){
			return null;
		}
		
		return $perm;
		
	}

	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}
?>