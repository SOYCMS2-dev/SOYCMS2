<?php
/**
 * @title ディレクトリ一覧
 */
class page_page_detail extends SOYCMS_WebPageBase{
	
	protected $id;
	protected $arg;
	protected $page;
	
	function doPost(){
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->page->getId(),true)){
			$this->goError();
			exit;
		}
		
		if(isset($_POST["save_basic"])){
			
			try{
				SOY2::cast($this->page,(object)$_POST["Page"]);
				
				if(isset($_POST["object"])){
					$object = $this->page->getPageObject();
					SOY2::cast($object,(object)$_POST["object"]);
					$object->save();
				}
				
				//upload
				if(isset($_FILES["icon_upload"]) && $_FILES["icon_upload"]["size"] > 0){
					$ext = pathinfo($_FILES["icon_upload"]["name"]);
					$ext = $ext["extension"];
					if(move_uploaded_file($_FILES["icon_upload"]["tmp_name"], SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . "/" . $this->page->getCustomClassName() . "." . $ext)){
						$config = $this->page->getConfigObject();
						$config["icon"] = $this->page->getCustomClassName() . "." . $ext;
						$filepath = SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . "/" . $this->page->getCustomClassName() . "." . $ext;
						soy2_resizeimage($filepath,$filepath,32,32);
						
						$this->page->setConfigObject($config);
					}
				}
			
				//upload
				if(isset($_FILES["favicon_upload"]) && $_FILES["favicon_upload"]["size"] > 0){
					$ext = pathinfo($_FILES["favicon_upload"]["name"]);
					$ext = $ext["extension"];
					if(move_uploaded_file($_FILES["favicon_upload"]["tmp_name"], SOYCMS_SITE_DIRECTORY . "themes/icons/" . $this->page->getCustomClassName() . ".ico")){
						$config = $this->page->getConfigObject();
						$config["favicon"] = "themes/icons/" . $this->page->getCustomClassName() . ".ico";
						
						if(class_exists("Imagick")){
							$path = SOYCMS_SITE_DIRECTORY . "themes/icons/" . $this->page->getCustomClassName() . ".ico";
							$Imagick = new Imagick($path);
							$Imagick->cropThumbnailImage(32,32);
							$Imagick->setFormat('ico');
							$Imagick->writeImage($path);
						}
						
						//.icoの時だけ
						if($config["favicon"] && preg_match('/\.ico$/',$config["favicon"])){
							$this->page->setConfigObject($config);
						}
					}
				}
				
				$this->page->save();
				
				//ページの更新処理を行う
				$logic = SOY2Logic::createInstance("site.logic.page.SOYCMS_PageLogic");
				$logic->update($this->page);
				
				$this->jump("/page/detail/" . $this->id . "?updated#tab1");
				
			}catch(Exception $e){
				$this->jump("/page/detail/" . $this->id . "?failed#tab1");
			}
			
		}
		
		//アイコンのクリア
		if(isset($_POST["clear_icon"])){
			$config = $this->page->getConfigObject();
			if(isset($config["icon"]) && $config["icon"] != ($this->page->getType() . ".gif")){
				$filepath = SOYCMS_ROOT_DIR . "content/" . SOYCMS_LOGIN_SITE_ID . "/" . $config["icon"];
				if(file_exists($filepath))@unlink($filepath);	
				$config["icon"] = $this->page->getType() . ".gif";
				$this->page->setConfigObject($config);
				$this->page->save();
			}
			$this->jump("/page/detail/" . $this->id . "?updated#tab1");
		}
		
		if(isset($_POST["save_advanced"])){
			
			$this->jump("/page/detail/" . $this->id . "?updated#tpl_config");
			
		}
		
		//テンプレートの変更
		if(isset($_POST["change_page_template"])){
			$this->page->setTemplate($_POST["Page"]["template"]);
			$this->page->save();
			$this->jump("/page/detail/" . $this->id . "?updated");
			exit;
		}
		
		//新しいブロックの追加
		if(isset($_POST["new_block"]) && strlen(@$_POST["new_block_id"])>0){
			$this->jump("/page/block/create?page=" . $this->id . "&id=" . $_POST["new_block_id"] . "&type=" . $_POST["new_block_type"]);
			exit;
		}
		
		//テンプレートの要素を変更する場合
		if(
			isset($_POST["new_skelton"])
		||	isset($_POST["save_template"])
		||	isset($_POST["save_property"])
		||  isset($_POST["NewItem"])
		||	isset($_POST["box"])
		||	isset($_POST["ItemDelete"])
		){
			$this->template = SOYCMS_Template::load($this->page->getTemplate());
			$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateUpdateLogic");
			
			$suffix = null;
			
			//テンプレートの保存
			if(isset($_POST["save_template"]) && isset($_POST["Template"])){
				//テンプレートのHTMLを保存する
				$this->template->setTemplate($_POST["Template"]["template"]);
				$logic->updateTemplate($this->template);
				
				$suffix = "tab3";
			}
			
			//プロパティの保存
			if(isset($_POST["save_property"]) && isset($_POST["Template"])){
				//テンプレートのプロパティ
				$content = $_POST["Template"]["property"];
				file_put_contents($this->template->getPropertyFilePath(),$content);
				
				$suffix = "tab3";
			}
			
			if(isset($_POST["new_skelton"]) && isset($_POST["add_new_skelton"])){
				$id = $_POST["new_skelton"];
				$logic->addNewLayout($this->template,$id);
			}
			
			if(count(@$_POST["NewItem"]) > 0){
				$logic->addNewItems($this->template,$_POST["NewItem"]);
			}
			
			if(isset($_POST["box"])){
				$logic->updateLayoutConfig($this->template,$_POST["box"]);
			}
			
			if(isset($_POST["ItemDelete"]) && is_array($_POST["ItemDelete"])){
				$config = $this->page->loadItemConfig();
				
				foreach($_POST["ItemDelete"] as $key => $value){
					if(!isset($config[$key]))$config[$key] = array();
					$config[$key]["hidden"] = $value;
				}
				
				$this->page->saveItemConfig($config);
			}
			
			if(isset($_POST["properties"]) && is_array($_POST["properties"])){
				$this->page->setProperties($_POST["properties"]);
			}
			
			$this->jump("/page/template/check?id=" . $this->page->getTemplate() . "&page=" . $this->id . "&suffix=" . $suffix);
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
		
		if(isset($_GET["entry"])){
			$page = $this->page;
			$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
			
			if($page->isDirectory()){
				$this->jump("/entry/list/" . $page->getId());
				exit;
			}else{
				$entries = $dao->getByDirectory($page->getId());
				if(count($entries) == 1){
					$entry = array_shift($entries);
					$this->jump("/entry/detail/" . $entry->getId());
				}
			}
		}
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->page->getId())){
			$this->goError();
			exit;
		}
		
		parent::prepare();
	}
	
	function page_page_detail($args){
		$this->id = @$args[0];
		$this->arg = @$args[1];
		
		WebPage::WebPage();
		
		$this->buildTab();
		$this->buildPage();
		$this->buildTabContent();
		
	}
	
	function buildTab(){
		$link = soycms_create_link("page/detail/" . $this->id);
		$this->addLink("tab_info_link",array("link" => $link . "#tab0"));
		$this->addLink("tab_basic_link",array("link" => $link . "#tab1"));
		$this->addLink("tab_item_link",array("link" => $link . "#tpl_config"));
		$this->addLink("tab_template_link",array("link" => $link . "#tab3"));
		$this->addLink("tab_editor_link",array("link" => soycms_create_link("page/detail/editor/" . $this->id)));
		$this->addLink("tab_customfield_link",array("link" => soycms_create_link("page/detail/field/" . $this->id)));
		
		PluginManager::load("soycms.site.page.config");
		$list = PluginManager::invoke("soycms.site.page.config",array(
			"page" => $this->page,
			"mode" => "menu"
		))->getList();
		
		$extLink = soycms_create_link("page/detail/ext/" . $this->id);
		$this->addList("ext_menu_list",array(
			"list" => $list,
			'populateItem:function($entity,$index)' => '$this->addLink("ext_menu_link",array("link"=>"'.$extLink.'/".$index,"text"=>$entity));' .
					'$this->addModel("ext_menu_link_wrap",array("class" => ($index == "'.$this->arg.'") ? "on" : ""));'
		));
		
	}
	
	function buildPage(){
		
		$this->addLabel("page_name",array("text"=>$this->page->getName()));
		$this->addLabel("page_uri_text",array("text"=>$this->page->getUri()));
		$this->addLink("page_uri_link",array("link" => soycms_get_page_url($this->page->getUri())));
	
		$this->addModel("window_title_wrap",array(
			"attr:id" => ($this->page->getType() == "detail") ? "window-title-directory" : "window-title-article"
		));
	}
	
	function buildTabContent(){
		
		$this->createAdd("page_info","page.detail.page_page_detail_info",array(
			"arguments" => array($this->id,$this->page)
		));
		
		$this->addForm("basic_config_form",array(
			"enctype" => "multipart/form-data"
		));
		$this->createAdd("basic_config","page.detail.page_page_detail_basic",array(
			"arguments" => array($this->id,$this->page)
		));
		
		$this->createAdd("item_config","page.detail.page_page_detail_item",array(
			"arguments" => array($this->id,$this->page)
		));
		
	}
}