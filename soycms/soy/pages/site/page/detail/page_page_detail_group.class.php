<?php
/**
 * @title ディレクトリ一覧
 */
class page_page_detail_group extends SOYCMS_WebPageBase{
	
	private $id;
	private $page;
	
	function doPost(){
		
		if(isset($_POST["GroupPermission"])){
			
			$dao = SOY2DAOFactory::create("SOYCMS_GroupPermissionDAO");
			$dao->begin();
			$dao->deleteByPageId($this->page->getId());
			foreach($_POST["GroupPermission"] as $groupId => $array){
				$obj = new SOYCMS_GroupPermission();
				$obj->setGroupId($groupId);
				$obj->setPageId($this->page->getId());
				$obj->setReadable(@$array["readable"]);
				$obj->setWritable(@$array["writable"]);
				$dao->insert($obj);
			}
			$dao->commit();
			
			$this->jump("/page/detail/group/" . $this->id . "?updated");
		}
		
	}

	function prepare(){
		$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
		
		if(isset($_GET["index"])){
			$uri = $this->page->getIndexUri();
			
			try{
				$index = SOY2DAO::find("SOYCMS_Page",array("uri" => $uri));
				$this->jump("/page/detail/" . $index->getId());
			}catch(Exception $e){
				$this->jump("/page/create?type=page&parent=" . $this->id . "&index");
			}
		}
		
		parent::prepare();
	}
	
	function page_page_detail_group($args){
		$this->id = @$args[0];
		WebPage::WebPage();
		
		
		$this->addForm("form");
		
		$this->buildPage();
		
	}
	
	function buildPage(){
		
		$this->addLabel("page_name",array("text"=>$this->page->getName()));
		$this->addLabel("page_uri_text",array("text"=>$this->page->getUri()));
		$this->addLink("page_uri_link",array("link" => soycms_get_page_url($this->page->getUri())));
		
		$this->addModel("window_title_wrap",array(
			"attr:id" => ($this->page->getType() == "detail") ? "window-title-directory" : "window-title-article"
		));
		
		/* ここから追加 */
		
		$this->addLink("return_link",array(
			"link" => soycms_create_link("/page/detail/" . $this->id)
		));
		
		/* permission */
		
		$this->createAdd("group_permission_list","_class.list.GroupPermissionList",array(
			"pageId" => $this->page->getId()
		));
		
	}
}