<?php
/*
 * 管理画面のメニューを拡張する
 */
class Plus_UserPageExtension implements SOY2PluginAction{
	
	/**
	 * @return string
	 */
	function getTitle($arguments){
		
	}
	
	function getMenuTitle(){
		
	}
	
	function getPage($arguments){
		
	}
	
	function doPost(){
		
	}
}
class Plus_UserPageExtensionDelegateAction implements SOY2PluginDelegateAction{
	
	private $mode = "list";
	private $menus = array();
	
	private $title;
	private $page;
	
	private $arguments = array();

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		if($this->mode == "list"){
			$this->menus[] = array($action->getMenuTitle(),$moduleId);
			$action = null;
		}else if($this->mode == "post"){
			$action->doPost();
		}else{
			$this->title = $action->getTitle($this->arguments);
			$this->page = $action->getPage($this->arguments);
		}
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}

	function getMenus() {
		return $this->menus;
	}
	function setMenus($menus) {
		$this->menus = $menus;
	}

	public function getMode(){
		return $this->mode;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setTitle($title){
		$this->title = $title;
		return $this;
	}

	public function getPage(){
		return $this->page;
	}

	public function setPage($page){
		$this->page = $page;
		return $this;
	}

	public function getArguments(){
		return $this->arguments;
	}

	public function setArguments($arguments){
		$this->arguments = $arguments;
		return $this;
	}
}
PluginManager::registerExtension("plus.user.page","Plus_UserPageExtensionDelegateAction");
