<?php
/**
 * @title 記事の作成
 */
class page_entry_create extends SOYCMS_WebPageBase{

	function page_entry_create($args){
		
		//記事のタイトル
		$title = (isset($_GET["title"]) && strlen($_GET["title"])>0) ? $_GET["title"] : "新しい記事";
		
		$entry = new SOYCMS_Entry();
		$entry->setTitle($title);
		$entry->setTitleSection($title);
		
		//新しいページ
		if(isset($_GET["parent"])){
			$this->createNewPage($entry);
			$this->jump("/entry/detail/" . $entry->getId());
		}
		
		//引数ある場合はディレクトリが決まっている場合
		if(count($args)>0){
			
			try{
				//「blank」が指定されていない時は雛形が0個の時だけOK
				if(!isset($_GET["blank"]) && count($args) == 1){
					$count = SOY2DAOFactory::create("SOYCMS_EntryTemplateDAO")->countByDirectory($args[0]);
					if($count != 0){
						throw new Exception("select entry template");
					}	
				}
				
				$id = $args[0];
				$page = SOY2DAO::find("SOYCMS_Page",$id);
				$entry->setDirectory($id);
				$entry->setDirectoryUri($page->getUri());
				
				//一度保存
				$entry->save();
				
				if($page->getType() == "detail"){
					$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
					
					if($page->getObject()->getEntryPosition() > 0){
						$order = $dao->countByDirectory($id);
						$entry->setOrder($order);
					}else{
						$entry->setOrder(0);
						$dao->updateOrders();
					}
					
					try{
						//page
						$obj = $page->getPageObject();
						$defaultTitle = $obj->getEntryTitle();
						
						$entry->setTitle($defaultTitle);
						$entry->setTitleSection($defaultTitle);
						
						$uri = $obj->getEntryUri($entry);
					}catch(Exception $e){
						$uri = "entry_" . $entry->getId() . ".html";
					}
					
					$entry->setUri($uri);
					
					//雛形が指定されている場合
					if(count($args) == 2){
						
						$template = SOY2DAO::find("SOYCMS_EntryTemplate",$args[1]);
						$entry = $template->buildEntry($entry);
						$template = null;	//clear
						$entry->save();
					}
				}
				
				//ラベルが指定されている場合
				if(isset($_GET["label"])){
					try{
						$label = SOY2DAO::find("SOYCMS_Label",$_GET["label"]);
						//ディレクトリが一致していた場合
						//ラベルを貼り付ける
						if($label->getDirectory() && $entry->getDirectory()){
							SOYCMS_Label::setLabel($entry->getId(),$label->getId(),$label);
							$uri = $label->getAlias() . "/" . $entry->getUri();
							$entry->setUri($uri);
							$uri = $obj->getEntryUri($entry);
							$entry->setUri($uri);
							$entry->save();
						}
					}catch(Exception $e){
						
					}
				}
				
				//addHistory
				$entry->save();
				
				//属性の保存
				if(isset($_GET["attribute"]) && is_array($_GET["attribute"])){
					foreach($_GET["attribute"] as $key => $value){
						SOYCMS_EntryAttribute::put($entry->getId(), $key, $value);
					}
				}
				
				$this->jump("/entry/detail/" . $entry->getId() . "?created");
				
			}catch(Exception $e){
				
			}
			
		}
		
		WebPage::WebPage();
		
		//ツリーを表示
		$this->createAdd("directory_tree","_class.list.EntryTreeComponent",array(
		));
		
		$this->addLabel("entry_create_attribute",array(
			"html" => (!empty($_GET["attribute"])) ? json_encode($_GET["attribute"]) : "{}"
		));
	}
	
	/**
	 * ページを取得
	 */
	function getPages(){
		$dao = SOY2DAOFactory::create("SOYCMS_PageDAO");
		$pages = $dao->get();
		return $pages;
	}
	
	/**
	 * 新しいページを作成
	 */
	function createNewPage($entry){
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$parent = $dao->getById($_GET["parent"]);
		
		//基本的に1階層と見なす
		if($parent->getParent()){
			$parent = $dao->getById($parent->getParent());
		}
		
		$title = $parent->getTitle();
		
		$child = $dao->getByParent($parent->getId());
		$count = count($child) + 2;//親+自分で2
		
		$entry->setParent($parent->getId());
		$entry->setDirectory($parent->getDirectory());
		$entry->setOrder(count($child));
		$entry->save();
		
		try{
			//page
			$page = SOY2DAO::find("SOYCMS_Page",$parent->getDirectory());
			$obj = $page->getPageObject();
			$title = $obj->getDefaultTitle();
			
			$uri = $obj->getEntryUri($entry);
		}catch(Exception $e){
			$uri = "entry_" . $entry->getId() . ".html";
		}
		
		$entry->setUri($uri);
		
		$entry->save();
		$entry->setTitle("{$title}(".$count.")");
		$entry->setTitleSection("{$title}(".$count.")");
		
		$entry->save();
	}
}