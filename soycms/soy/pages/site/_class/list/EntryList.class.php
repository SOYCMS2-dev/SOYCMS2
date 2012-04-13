<?php

class EntryList extends HTMLList{
	
	private $detailLink;
	private $listLink;
	private $operationLink;
	private $workflow;
	private $mapping = array();
	
	function init(){
		
		$this->operationLink = soycms_create_link("/entry/operation") . "?mode=sort&";
		$this->detailLink = soycms_create_link("/entry/detail");
		$this->listLink = soycms_create_link("/entry/list/");
		$this->mapping = SOYCMS_DataSets::get("site.page_mapping",array());
		
		$this->_soy2_parent->addModel("entry_exists",array("visible"=>count($this->list)>0));
		$this->_soy2_parent->addModel("entry_not_exists",array("visible"=>count($this->list)<1));
		
		$workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		$workflow->load(); 
		$this->workflow = $workflow;
	}

	function populateItem($entity){
 		
 		$dirURL = (isset($this->mapping[$entity->getDirectory()])) ? $this->mapping[$entity->getDirectory()]["uri"] : null;
 		
 		if($dirURL){
	 		$dirURL = SOYCMS_SITE_ROOT_URL . (($dirURL == "_home") ? "" : $dirURL);
	 		if(strlen($entity->getUri())>0 && $dirURL[strlen($dirURL)-1] != "/")$dirURL .= "/";
 		}
 		
 		$this->addCheckbox("entry_checkbox",array(
 			"name" => "entryIds[]",
 			"value" => $entity->getId()
 		));
 		
 		$this->addLink("entry_detail_link",array(
 			"link" => $this->detailLink . "/" . $entity->getId(),
 		));
 		$this->addModel("entry_detail_link_wrap",array(
 			"class" => (strpos($_SERVER["REQUEST_URI"],$this->detailLink . "/" . $entity->getId()) === 0) ? "on" : "",
 			"style" => ($entity->getPublish() < 0) ? "text-decoration:line-through;color:gray;" : ""
 		));
 		$this->addLink("sort_up_link",array(
 			"link" => $this->operationLink . "id=" . $entity->getId() . "&diff=-1",
 			"onclick" => "return false;"
 		));
 		$this->addLink("sort_down_link",array(
 			"link" => $this->operationLink . "id=" . $entity->getId() . "&diff=+1",
 			"onclick" => "return false;"
 		));
 		
 		$this->addLink("directory_link",array(
 			"link" => $this->listLink . $entity->getDirectory(),
 			"text" => (isset($this->mapping[$entity->getDirectory()])) ? $this->mapping[$entity->getDirectory()]["name"] : "",
 		));
 		
 		$this->addLabel("label_link_list",array(
 			"html" => ($entity instanceof SOYCMS_Entry) ? $this->getLabelLink($entity) : ""
 		));
 		
 		$url = ($dirURL) ? $dirURL . rawurldecode($entity->getUri()) : rawurldecode($entity->getUri());
 		if(mb_strwidth($url) > 50){
 			$url = mb_strimwidth($url,0,36,"...") . mb_strimwidth($url,mb_strwidth($url) - 14,99999);
 		}
 		$this->addLabel("entry_url",array(
 			"text" => $url
 		));
 		
 		$this->addLabel("entry_memo",array(
 			"text" => $entity->getMemo()
 		));
 		
 		$this->addLink("entry_preview_link",array(
 			"link" => ($dirURL)
 			 ? (($entity->isOpen()) ? $dirURL . $entity->getUri() : $dirURL . $entity->getUri() . "?preview&" . soycms_get_ssid_token()) 
 			 : ""
 		));
 		
 		$this->addLabel("entry_title",array(
 			"text" => mb_strimwidth($entity->getTitle(),0,40,"...")
 		));
 		
 		$this->createAdd("entry_update_date","_class.component.SimpleDateLabel",array(
 			"date" => $entity->getUpdateDate() 
 		));
 		
 		$this->addLabel("entry_status",array(
 			"text" => $this->workflow->getStatusText($entity->getStatus())
 		));
 		
 		$this->addModel("entry_list_row",array(
 			"class" => ($entity->getPublish() < 0) ? "entry_trashed" : ""
 		));
 		
 		//ヒストリーの表示
 		$this->buildHistory($entity->getId());
	}
	
	/**
	 * ラベルのリンクを生成
	 */
	function getLabelLink($entity){
		$array = array();
		$labels = SOYCMS_Label::getByEntryId($entity->getId());
		$labelLink = $this->listLink . $entity->getDirectory() . "/";
		
		foreach($labels as $label){
			$array[] = '<a href="'.$labelLink.$label->getId().'">'.$label->getName().'</a>';
		}
		
		return implode("|",$array);
	}
	
	/**
	 * 履歴を生成
	 */
	function buildHistory($id){
		static $_dao;
		if(!$_dao)$_dao = SOY2DAOFactory::create("SOYCMS_EntryHistoryDAO");
		static $users;
		if(!$users)$users = SOY2DAO::find("admin.domain.SOYCMS_User");
		
		//作成者
		$histories = $_dao->getByEntryId($id,"create");
		$history = (count($histories)>0) ? $histories[0] : new SOYCMS_EntryHistory();
		
		$this->addLabel("entry_author",array(
			"text" => (isset($users[$history->getAdminId()])) ? $users[$history->getAdminId()]->getName() : "-"
		));
		
		//更新者
		$histories = $_dao->getByEntryId($id,"update");
		$editors = array();
		if(count($histories)>3){
			$editors[] = "他" . count($histories) . "人のユーザ";
		}else{
			foreach($histories as $history){
				$editors[] = (isset($users[$history->getAdminId()])) ? $users[$history->getAdminId()]->getName() : "-";
			}
		}
		
		$this->addLabel("entry_editor",array(
			"text" => (count($editors)>0) ? " / " . implode(",",$editors) : ""
		));
		
	}
}
?>