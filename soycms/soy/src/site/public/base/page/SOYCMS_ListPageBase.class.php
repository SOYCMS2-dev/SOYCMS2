<?php

class SOYCMS_ListPageBase extends SOYCMS_SitePageBase{
	
	private $label;
	private $checkLabel = true;
	
	function SOYCMS_ListPageBase($args = array()){
		$this->setPageObject($args["page"]);
		$this->setArguments($args["arguments"]);

		WebPage::WebPage();
	}

	function build($args){
		
		$page = $this->getPageObject();
		$listPage = $page->getPageObject();
		
		
		//表示非表示チェック
		$visible = $this->isItemVisible("default:entry_list");
		
		//絞り込みパラメーター
		$label = null;
		$tag = null;
		$dirId = $listPage->getDirectory();
		$entries = array();
		$total = 0;
		$currentPage = -1;
		$pagerUrl = soycms_get_page_url($page->getUri()) . "/";	//ページャー用のURL
		
		
			
		//プラグインを指定しているかどうかで振り分け
		if($listPage->getPlugin()){
			//表示中のみ検索する
			if($visible){
				$plugin = $listPage->getPluginObject();
				list($entries,$total) = $plugin->getEntries($this,$args);
			}
		
		//標準動作
		}else if($this->checkLabel){
		
			$limit = $listPage->getLimit();
			
			
			if(count($args)>0){
				if($args[0] == "tag"){
					array_shift($args);
					$tag = array_shift($args);
					SOYCMS_Helper::set("current_tag", $tag);
				}else if(is_numeric($args[count($args)-1])){
					$currentPage = array_pop($args);
				}else if(preg_match("/page-([0-9]+)/",$args[count($args)-1],$tmp)){
					$currentPage = array_pop($args);
				}
				
				//ラベルの取得
				if(count($args) > 0){
					try{
						$labelDAO = SOY2DAOFactory::create("SOYCMS_LabelDAO");
						$alias = rawurldecode(implode("/",$args));
						$label = $labelDAO->getByParams($dirId,$alias);
						$this->label = $label;
						$label = $label->getId();
					}catch(Exception $e){
						throw new SOYCMS_NotFoundException();	//go 404
					}
				}
				
				//1ページ目の時は正規化のためにredirect
				if($currentPage == 1){
					$uri = ($page->getUri() == "index.html") ? $page->getParentDirectoryUri() : $page->getUri();
					$url = soycms_get_page_url($uri);
					
					//ラベルを指定している時
					if($this->label){
						$url = soycms_get_page_url($uri,$this->label->getAlias());
					}
					
					SOY2PageController::redirect($url);
				}
				
			}
			
			if($currentPage < 0)$currentPage = 1;
			$offset = max(0,($currentPage-1) * $limit);
			
			//表示中のみ検索する
			if($visible){
				list($entries,$total) = $this->getEntries($offset,$label,$tag);
			}
			
			if($this->label){
				$pagerUrl = soycms_get_page_url($page->getParentDirectoryUri(),$this->label->getAlias()) . "/";
			}
		}
		
		//記事の追加リンク
		$query = null;
		
		if(count($dirId)>0){
			if($listPage->getIsIncludeChild()){
				$query = "?dirId=" . $dirId[0];
			}else{
				$query = "/" . $dirId;
			}
		}
		
		$this->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => $entries,
			"directory" => $dirId,
			"directoryUri" => $page->getParentDirectoryUri(),
			"soy2prefix" => "block",
			"link" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/entry/create" . $query : "",
			"configLink" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/page/detail/" . $page->getId() . "#tab1/advance" : "",
			"visible" => $visible
		));
		
		
		$limit = $listPage->getLimit();
		$this->createAdd("pager","SOYCMS_ListPageBase_HTMLPager",array(
			"start" => ($currentPage - 1) * $listPage->getLimit() + 1,
			"page" => $currentPage,
			"total" => $total,
			"limit" => $limit,
			"link" => $pagerUrl,
			"soy2prefix" => "block",
			"childPrefix" => "cms",
			"visible" => $this->isItemVisible("default:pager") && ($limit < $total)
		));
		
		/* ラベル周り */
		
		$this->addModel("label_selected",array(
			"soy2prefix" => "cms",
			"visible" => ($this->label)
		));
		
		$this->addModel("label_not_selected",array(
			"soy2prefix" => "cms",
			"visible" => (!$this->label)
		));
		
		$this->addLabel("selected_label_name",array(
			"soy2prefix" => "cms",
			"text" => ($this->label) ? $this->label->getName() : ""
		));
		
		$config = ($this->label) ? $this->label->getConfigObject() : array();
		
		$this->addLabel("selected_label_description",array(
			"soy2prefix" => "cms",
			"html" => ($this->label) ? @$config["description"] : ""
		));
		
		$this->createAdd("current_directory_label","SOYCMS_LabelListComponent",array(
			"list" => ($this->label) ? array($this->label) : array(),
			"visible" => ($this->label) ? true : false,
			"soy2prefix" => "block",
			"mode" => "current",
			"labelId" => ($this->label) ? $this->label->getId() : null
		));
		
		if($this->label){
			$pageConfig = $page->getConfigObject();
			$pageConfig["title"] = (@$config["title"]) ? @$config["title"] : "#LabelName# - #DirName# - #SiteName#";
			$page->setConfigObject($pageConfig);
		}
	}
	
	/**
	 * @return array(entries,total)
	 */
	function getEntries($offset,$label,$tag){
		$page = $this->getPageObject();
		$listPage = $page->getPageObject();
		
		return $listPage->getEntries($offset,$label,$tag);
	}
	
	function convertTitle($title){
		$title = parent::convertTitle($title);
		if($this->label){
			$title = str_replace("#LabelName#",$this->label->getName(),$title);
		}
		return $title;
	}
	
	/* getter setter */
	
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}

	function setCheckLabel($value){
		$this->checkLabel = $value;
	}
}

class SOYCMS_ListPageBase_HTMLPager extends SOYCMS_HTMLPager{
	
	 function execute(){
		
		if($this->_soy2_parent){
		
			//件数情報表示
			$this->_soy2_parent->createAdd("count_start","HTMLLabel",array(
				"text" => $this->getStart(),
				"soy2prefix" => "cms"
			));
			$this->_soy2_parent->createAdd("count_end","HTMLLabel",array(
				"text" => $this->getEnd(),
				"soy2prefix" => "cms"
			));
			$this->_soy2_parent->createAdd("count_max","HTMLLabel",array(
				"text" => $this->getTotal(),
				"soy2prefix" => "cms"
			));
		
		}

		//ページへのリンク
		$next = $this->getNextParam();
		$next["soy2prefix"] = "cms";
		$this->createAdd("next_link","HTMLLink",$next);
		$this->createAdd("next_link_wrap","HTMLModel",array(
				"visible" => $next["visible"],
				"soy2prefix"=>"cms"
		));
		$prev = $this->getPrevParam();
		$prev["soy2prefix"] = "cms";
		$this->createAdd("prev_link","HTMLLink",$prev);
		$this->createAdd("prev_link_wrap","HTMLModel",array(
				"visible" => $prev["visible"],
				"soy2prefix"=>"cms"
		));
		
		$param = $this->getPagerParam();
		$param["soy2prefix"] = "cms";
		$param["childSoy2Prefix"] = "cms";
		$this->createAdd("pager_list","SOY2HTMLPager_List",$param);

		//ページへジャンプ
		$this->createAdd("pager_jump","HTMLForm",array(
			"method" => "get",
			"action" => $this->getLink()
		));
		$this->createAdd("pager_select","HTMLSelect",array(
			"name" => "page",
			"options" => $this->getSelectArray(),
			"selected" => $this->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
		
		SOYBodyComponentBase::execute();
	}
}
?>