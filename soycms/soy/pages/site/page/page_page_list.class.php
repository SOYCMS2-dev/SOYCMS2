<?php
/**
 * @title ディレクトリ一覧
 */
class page_page_list extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["PageOrders"])){
			$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
			
			$counter = 0;
			foreach($_POST["PageOrders"] as $key => $value){
				$page = $dao->getById($key);
				$config = $page->getConfigObject();
				$config["order"] = $counter;
				$counter++;
				$page->setConfigObject($config);
				$dao->update($page);
				$page = null;
			}
			
			//update mapping
			SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic")->updatePageMapping();
			
			
			$this->jump("/page/list");
			
		}
		
	}

	function page_page_list($arg = null){
		
		if($arg){
			$this->setId($arg[0]);
			$this->setPageParam($arg[1]);
		}
		
		WebPage::WebPage();
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		
		$this->createAdd("page_list","_class.list.PageTreeComponent",array(
			"list" => $pages
		));		
		
		$this->addForm("form",array("action"=>soycms_create_link("page/list")));
		
		$this->addLabel("menu_title",array(
			"text" => ($arg) ? "サイトマップ" : "ディレクトリ管理"
		));
		
		$this->createAdd('template_list',"_class.list.TemplateList",array(
			"types" => array_keys(SOYCMS_Template::getTypes())
		));
		
		$session = SOY2Session::get("site.session.SiteLoginSession");
		
		$this->addModel("not_designer",array(
			"visible" => ($session->hasRole("designer")) ? false : true
		));
		$this->addModel("not_editor",array(
			"visible" => ($session->hasRole("editor")) ? false : true
		));
	}
	
	function getLayout(){
		return ($this->_soy2_parent) ? "blank.php" : "default.php"; 
	}
}