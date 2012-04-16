<?php
/*
 * 管理画面にページを追加する
*/
class SOYCMS_SiteObjectExtension implements SOY2PluginAction{

	private $moduleId = null;

	function getType(){
		return $this->getModuleId();
	}

	function getName(){
		return $this->getModuleId();
	}

	/**
	 * 表示時に呼ばれる
	 * @param SOYCMS_Object $obj
	 */
	function display(SOYCMS_Object $obj){

	}
	
	/**
	 * 保存時に呼ばれる
	 * @param SOYCMS_Object $obj
	 */
	function onUpdate(SOYCMS_Object $obj){
		
	}
	
	/**
	 * 削除時に呼ばれる
	 * @param SOYCMS_Object $obj
	 */
	function onDelete(SOYCMS_Object $obj){
		
	}
}

class SOYCMS_SiteObjectExtensionDelegateAction implements SOY2PluginDelegateAction{

	private $mode = "page";
	private $moduleId = null;
	private $list = array();

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			case "display":
				
			case "list":
				$this->list[$action->getType()] = array(
					"name" => $action->getName(),
					"extensionId" => $extensionId
				);
				break;
		}
		
	}
	
	/* getter setter */
}
PluginManager::registerExtension("soycms.site.object","SOYCMS_SiteObjectExtensionDelegateAction");