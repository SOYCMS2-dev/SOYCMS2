<?php
/**
 * @title ディレクトリ一覧
 */
class page_page_error extends SOYCMS_WebPageBase{
	
	function init(){
		if(isset($_GET["id"]) && $_GET["type"]){
			$page = SOY2DAO::find("SOYCMS_Page",$_GET["id"]);
			$uri = $page->getUri();
			$uri = soycms_union_uri($uri, $_GET["type"] . ".html");
			
			try{
				$page = SOY2DAO::find("SOYCMS_Page",array("uri" => $uri));
				$page->setType(".error");
				$page->save();
				
			}catch(Exception $e){
				switch($_GET["type"]){
					case "500":
						$name = "500 Internal Server Error";
						break;
					case "403":
						$name = "403 Forbidden";
						break;
					default:
					case "404":
						$name = "404 Not Found";
						break;
				}
				
				$page = new SOYCMS_Page();
				$page->setType(".error");
				$page->setName($name);
				$page->setUri($uri);
				$page->save();
				
				//template
				$templateId = $this->getErrorPageTemplateId();
				$page->setTemplate($templateId);
				$page->save();
				
				
				//save object
				$obj = $page->getObject();
				$obj->setStatusCode($_GET["type"]);
				$obj->save();
				
				
				SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic")->updatePageMapping();
			}
			
			
			$this->jump("/page/detail/" . $page->getId());
		}
	}
	
	function page_page_error($arg = null){
		
		
		WebPage::WebPage();
		
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		
		$this->createAdd("page_list","ErrorPageTree",array(
			"list" => $pages,
		));		
		
	}
	
	function getErrorPageTemplateId(){
		$templates = SOYCMS_Template::getListByType(".error");
		$templates = array_keys($templates);
		
		if(count($templates) > 0){
			return array_shift($templates);
		}
		
		return null;
	}
}

SOY2HTMLFactory::importWebPage("_class.list.PageTreeComponent");
class ErrorPageTree extends PageTreeComponent{
	
	private $link;
	
	function init(){
		$this->link = soycms_create_link("/page/error");
		parent::init();
	}
	
	/**
	 * ページを並び替える(usort function)
	 */
	function sortPages($a,$b){
		$order_a = $a["object"]["page_uri"];
		$order_b = $b["object"]["page_uri"];
		return ($order_a > $order_b);
	}
	
	function populateItem($entity,$key,$depth,$isLast){
		parent::populateItem($entity,$key,$depth,$isLast);
		
		$this->addLink("error_page_403",array(
			"link" => $this->link . "?id=".$entity->getId()."&type=403",
		));
		$this->addLink("error_page_404",array(
			"link" => $this->link . "?id=".$entity->getId()."&type=404",
		));
		$this->addLink("error_page_500",array(
			"link" => $this->link . "?id=".$entity->getId()."&type=500",
		));
		
		if(!$entity->isDirectory() && $entity->getType() != ".error"){
			return false;
		}
		
		return true;
		
	}
	
}