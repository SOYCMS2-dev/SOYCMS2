<?php
/**
 * @title ディレクトリ削除
 */
class page_page_remove extends SOYCMS_WebPageBase{
	
	private $id;
	
	function doPost(){
		if(isset($_POST["next"])){
			$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
			$mapping = SOYCMS_DataSets::get("site.page_mapping",array());
			$page = $dao->getById($this->id);
			$uri = $page->getUri();
			
			foreach($mapping as $id => $array){
				$url = $array["uri"];
				if(strpos($url,$uri) === 0){
					$tmpPage = $dao->delete($array["id"]);
				}
			}
			
			$logic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
			$logic->updatePageMapping();
			
			if($page->getType() == ".error"){
				$this->jump("/page/error?deleted");
			}else{
				$this->jump("/page/list?deleted");
			}
		}
		
		$page = $dao->getById($this->id);
		if($page->getType() == ".error"){
			$this->jump("/page/error");
		}else{
			$this->jump("/page/list");
		}
		
		
	}

	function page_page_remove($args){
		$this->id = $args[0];
		$this->page = SOY2DAO::find("SOYCMS_Page",$this->id);
		
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->createAdd("page_info","page.detail.page_page_detail_info",array(
			"arguments" => array($this->id,$this->page,"remove")
		));
		
		$this->addLabel("page_name",array("text"=>$this->page->getName()));
	}
}