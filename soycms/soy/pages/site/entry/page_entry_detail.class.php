<?php
SOY2::import("site.logic.entry.SOYCMS_EditorManager");

/**
 * @title 記事の作成
 */
class page_entry_detail extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->dir->getId(),true)){
			if(isset($_GET["autosave"])){
				echo "permission error";
				exit;
			}else{
				$this->goError();
			}
			exit;
		}
		
		//ゴミ箱
		if(isset($_POST["op_trash"]) || isset($_POST["op_delete"]) || isset($_POST["op_recover"])){
			if(soy2_check_token()){
				switch(true){
					case isset($_POST["op_trash"]):
						$this->entry->setPublish(-1);
						$this->entry->save();
						break;
					case isset($_POST["op_recover"]) && $this->entry->getPublish() < 1:
						$this->entry->setPublish(0);
						$this->entry->save();
						break;
					case isset($_POST["op_delete"]) && $this->entry->getPublish() < 1:
						$this->entry->delete();
						$this->jump("/entry/list/" . $this->dir->getId() . "?deleted");
						break;
				}
				
				$this->jump("/entry/detail/" . $this->entry->getId() . "?updated");
			}
			
			$this->jump("/entry/detail/" . $this->entry->getId() . "?failed");
		}
		
		
		//EntryLogic
		$logic = SOY2Logic::createInstance("site.logic.entry.SOYCMS_EntryLogic");
		
		if(isset($_POST["action"])){
			$action = $_POST["action"];
			$workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
			$workflow->load();
			
			try{
				$session = SOY2Session::get("site.session.SiteLoginSession");
				$action = $workflow->getAction($action,$this->entry->getStatus(),$session->getRoles());
				$action->execute($this->entry);
				
				/*
				 * ページの設定と同期をする
				 */
				$logic->onUpdate($this->entry);
				
				
				$this->jump("/entry/detail/" . $this->entry->getId() . "?updated");
			
			}catch(SOY2MailException $e){
				//メール送信に失敗
				$this->jump("/entry/detail/" . $this->entry->getId() . "?updated");
				
			}catch(Exception $e){
				//無効なActionが選択された時もException
				$this->jump("/entry/detail/" . $this->entry->getId() . "?failed");
			}
			
		}
		
		//削除
		if(isset($_POST["remove_page"])){
			$id = $this->entry->getParent();
			$this->entry->delete();
			$this->jump("/entry/detail/" . $id . "?updated");
		}
		
		if(isset($_POST["Entry"])){
			
			SOY2::cast($this->entry,(object)$_POST["Entry"]);
			$entry = $this->entry;
			$entry->setId($this->id);
			
			if(isset($_POST["entry_status_open"])){
				$entry->setStatus(1);
			}else if(isset($_POST["entry_status_close"])){
				$entry->setStatus(0);
			}
			
			//自動保存 今すぐ保存の場合
			if(isset($_GET["autosave"])){
				$logic->update($entry,false,false);
				echo date("H:i:s",$entry->getUpdateDate());
				exit;
			}
			
			//公開
			if(isset($_POST["do_open"])){
				$session = SOY2Session::get("site.session.SiteLoginSession");
				$roles = $session->getRoles();
				
				//公開権限が無い場合はそのままでリダイレクト
				if(!in_array("publisher",$roles)){
					$this->jump("/entry/detail/" . $this->entry->getId() . "?failed");
				}
				$this->entry->setStatus("open");
				$this->entry->setPublish(1);
				$entry = $this->entry;
			}
			
			
			//保存 公開
			$logic->update($entry,true);
			
			//カスタムフィールド(自動保存では保存しないようにする)
			PluginManager::load("soycms.site.entry.field");
			PluginManager::invoke("soycms.site.entry.field",array(
				"entry" => $this->entry,
				"mode" => "update"
			));
			
			PluginManager::load("soycms.site.entry.update");
			PluginManager::invoke("soycms.site.entry.update",array(
				"entry" => $this->entry
			));
			
			
			
			//customfield
			if(isset($_POST["EntryCustomField"])){
				SOYCMS_ObjectCustomField::setObjectValues(
						"entry",
						$this->id,
						$_POST["EntryCustomField"],
						"entry-" . $this->entry->getDirectoryUri()
				);
			}
			
			$this->jump("/entry/detail/" . $entry->getId() . "?updated");
			
		}
		
		
	}

	private $id;
	private $entry;
	private $dir;

	function page_entry_detail($args){
		$this->id = @$args[0];
		$this->entry = ($this->id) ? SOY2DAO::find("SOYCMS_Entry",($this->id)) : new SOYCMS_Entry;
		
		try{
			$this->dir = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			$this->dir = new SOYCMS_Page();
		}
		
		if(isset($_GET["preview"]) || isset($_GET["check"])){
			$link = soycms_get_page_url($this->dir->getUri(),$this->entry->getUri());
			if(isset($_GET["preview"]))$link .= "?" . soycms_get_ssid_token() . "&preview" . (strlen($_GET["preview"])>0 ? "=" . $_GET["preview"] : "");
			SOY2PageController::redirect($link);
			exit;
		}
		
		//permission check
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->checkPermission($this->dir->getId())){
			$this->goError();
			exit;
		}
		
		WebPage::WebPage();
		
		
		$this->createAdd("form","_class.form.EntryForm",array(
			"entry" => $this->entry
		));
		
		$this->buildPage();
		$this->buildScript();
		$this->buildEntryNavigation();
	}
	
	/**
	 * ページの要素を構築
	 */
	function buildPage(){
		$dir = $this->dir;
		
		$this->addLabel("entry_url",array(
			"text" => rawurldecode(soycms_union_uri(soycms_get_page_url($dir->getUri()),$this->entry->getUri()))
		));
		$this->addLabel("directory_url",array(
			"text" => rawurldecode(soycms_union_uri(soycms_get_page_url($dir->getUri())))
		));
		$this->addModel("in_directory",array(
			"visible" => ($dir->getId() && $dir->getType() == "detail")
		));
		$this->addLink("page_link",array(
			"link" => soycms_get_page_url($dir->getUri()),
			"text" => rawurldecode(soycms_get_page_url($dir->getUri()))
		));
		$this->addModel("is_page_entry",array(
			"visible" => ($dir->getId() && $dir->getType() != "detail")
		));
		
		$this->createAdd("update_date_text","_class.component.SimpleDateLabel",array(
			"date" => $this->entry->getUpdateDate()
		));
		
		$this->buildChildEntryNavigation();
		
		//ディレクトリ毎に要素の設定を読み込む
		$config = $dir->getConfigObject();
		$orders = (isset($config["append_snippet_order"])) ? $config["append_snippet_order"] : array();
		$allow = (isset($config["allowed_append_snippet"])) ? $config["allowed_append_snippet"] : array();
		$ordersInsert = (isset($config["insert_snippet_order"])) ? $config["insert_snippet_order"] : array();
		$allowInsert = (isset($config["allowed_insert_snippet"])) ? $config["allowed_insert_snippet"] : array();
		
		
		$this->addLabel("append_new_sections",array(
			"html" => SOYCMS_EditorManager::bulidSectionMenus($orders,$allow)
		));
		$this->addLabel("insert_new_sections",array(
			"html" => SOYCMS_EditorManager::buildInsertSectionMenus($ordersInsert,$allowInsert)
		));
		
		$this->addModel("add_child_entry_snippet",array(
			"visible" => (in_array("nextpage",$allow) || !in_array("nextpage",$ordersInsert))
		));
		
		//添付ファイル
		$this->createAdd("attachments","entry.page_entry_attachments",array(
			"arguments" => array($this->id,$this->entry)
		));
		
		//カスタムフィールド
		PluginManager::load("soycms.site.entry.field");
		$html = PluginManager::display("soycms.site.entry.field",array(
			"entry" => $this->entry
		));
		
		//共通カスタムフィールド
		$this->buildCommonCustomField($this->entry);
		
		$this->addLabel("soycms_entry_customfield",array(
			"html" => $html
		));
		
		$workflow = SOY2Logic::createInstance("site.logic.workflow.WrokflowManager");
		$workflow->load();
		$statusText = $workflow->getStatusText($this->entry->getStatus());
		
		$this->addModel("save_btn",array(
			"attr:value" => ($this->entry->getPublish() < 1) ? "保存(".$statusText.")" : "更新する"
		));
		
		$this->addModel("remove_page_btn",array(
			"attr:name" => "remove_page",
			"visible" => ($this->entry->getParent())
		));
		
		/*
		 * 公開後に自動保存で保存した場合に表示
		 */
		$overwrited = $this->entry->isOverwrited();
		
		$this->addModel("overwrite_warning",array(
			"visible" => $overwrited
		));
		
		$this->addLabel("entry_title_text",array(
			"text" => $this->entry->getTitle()
		));
		
		//clear
		SOYCMS_EditorManager::release();
	}
	
	/**
	 * 次のページとか
	 */
	function buildChildEntryNavigation(){
		$childEntries = SOY2DAO::find("SOYCMS_Entry",array("parent"=>
			($this->entry->getParent()) ? $this->entry->getParent() : $this->entry->getId()
		));
		
		$this->addModel("has_child_entry",array("visible"=>(count($childEntries)>0)));
		
		$parent = SOY2DAO::find("SOYCMS_Entry",($this->entry->getParent()) ? $this->entry->getParent() : $this->entry->getId());
		array_unshift($childEntries,$parent);
		
		$this->createAdd("child_entry_list","_class.list.EntryList",array(
			"list" => $childEntries
		));
		
		$this->addLink("add_child_entry_link",array(
			"link" => soycms_create_link("/entry/create?parent=" . $parent->getId())
		));
		
	}
	
	function buildEntryNavigation(){
		try{
			$directory = SOY2DAO::find("SOYCMS_Page",$this->entry->getDirectory());
		}catch(Exception $e){
			$directory = new SOYCMS_Page();
		}
		
		$dao = SOY2DAOFactory::create("SOYCMS_EntryDAO");
		$detail = soycms_create_link("/entry/detail");
		try{
			$prev = $dao->getPrevEntry($this->entry);
		}catch(Exception $e){
			$prev = null;
		}
		
		try{
			$next = $dao->getNextEntry($this->entry);
		}catch(Exception $e){
			$next = null;
		}
		
		$this->addLink("prev_entry_link",array(
			"link" => $detail . "/" . (($prev) ? $prev->getId() : ""),
			"visible" => (!is_null($prev)),
		));
		
		$this->addLabel("prev_entry_title",array(
			"text" => ($prev) ? $prev->getTitle() : ""
		));
		
		$this->addLink("next_entry_link",array(
			"link" => $detail . "/" . (($next) ? $next->getId() : ""),
			"visible" => (!is_null($next)),
		));
		
		$this->addLabel("next_entry_title",array(
			"text" => ($next) ? $next->getTitle() : ""
		));
		
		$this->addLink("list_link",array(
			"text" => $directory->getName(),
			"link" => soycms_create_link("/entry/list/" . $directory->getId())
		));
	}

	/**
	 * JavaScript関連を作成
	 */
	function buildScript(){
		
		$scripts = array();
		$scripts[] = "var EDITOR_ACTION_URL = \"".SOY2FancyURIController::createLink("/entry/editor")."?entryId=".$this->id."\";";
		$scripts[] = "var EDITOR_HISTORY_URL = \"".SOY2FancyURIController::createLink("/entry/history/list")."?entryId=".$this->id."\";";
		
		$customCSS = SOYCMS_DataSets::get("editor_custom_css","");
		
		if(strlen($customCSS)>0){
			$customCSS = implode(",",explode("\n",$customCSS));
			$scripts[] = '$(function(){';
			$scripts[] = 'aobata_editor.option.custom_css = "'.$customCSS.'";';
			$scripts[] = '});';
		}
		
		$this->createAdd("ajax_action","HTMLScript",array(
			"script" => implode("\n",$scripts)
		));
	}
	
	function buildCommonCustomField($entry){
		$fields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry");
		$fields2 = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $this->entry->getDirectoryUri());
		$fields = array_merge($fields2,$fields);
		$this->addModel("common_customfield_exists",array("visible" => count($fields) > 0));
		
		$this->createAdd("field_list","_class.list.CustomFieldList",array(
			"list" => $fields,
			"objectId" => $this->id,
			"formName" => "EntryCustomField",
			"values" => SOYCMS_ObjectCustomField::getValues("entry",$this->id)
		));
		
		$this->addLabel("content_position",array(
			"text" => $this->dir->getConfigParam("content-position")
		));
	}
	
	function getSubMenu(){
		$menu = SOY2HTMLFactory::createInstance("entry.page_entry_detail_submenu",array(
			"arguments" => array($this->id,$this->entry)
		));
		$menu->display();
		
	}
}


?>