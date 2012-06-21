<?php
/**
 * 記事を表示するためのコンポーネント
 * 記事を表示するには全てこのコンポーネントを使用すること
 * @author miyazawa
 */
class SOYCMS_EntryListComponent extends HTMLList{
	
	private $mode = "list";
	private $summary = 0;
	private $link = "";
	private $configLink = "";
	
	private $directory = null;
	private $directoryUri = null;
	
	public static function getMapping(){
		return SOYCMS_DataSets::load("site.page_mapping");
	}
	
	public static function getLabels(){
		static $labels = null;
		
		if(is_null($labels)){
			$labels = SOY2DAO::find("SOYCMS_Label");
		}
		
		return $labels;
	}
	
	public static function getUsers(){
		static $users = null;
		
		if(is_null($users)){
			$users = SOY2DAO::find("SOYCMS_User");
		}
		
		return $users;
	}
	
	function init(){
		static $_inited = false;
		
		if(!$_inited){
			PluginManager::load("soycms.site.entry.field");
		}
		
		$this->setChildSoy2Prefix("cms");
	}
	
	function getStartTag(){
		if($this->mode == "block")return parent::getStartTag();
		return '<?php SOYCMS_ItemWrapComponent::startTag("entry","'.$this->getId().'","'.$this->configLink.'","'.$this->link.'"); ?>' .
		 			parent::getStartTag();
		
	}

	
	function getEndTag(){
		if($this->mode == "block")return parent::getEndTag();
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
	function populateItem($entity,$key){
		
		if(false == ($entity instanceof SOYCMS_Entry)){
			$entity = new SOYCMS_Entry();
			$entity->setDirectory($this->directory);
			$entity->setId(-1);
		}
		
		//onOutput
		PluginManager::invoke("soycms.site.entry.output.list",array(
			"entry" => $entity
		));
		
		//カスタムフィールドプラグイン
		PluginManager::invoke("soycms.site.entry.field",array(
			"entry" => $entity,
			"htmlObj" => $this,
			"mode" => $this->mode
		));
		
		//カスタムフィールドの出力
		$this->buildCustomFields($entity);
		
		/* 表示 */
		
		$users = self::getUsers();
		$mappings = self::getMapping();
		
		$this->createAdd("entry_id","HTMLLabel",array(
			"text" => $entity->getId(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("title","HTMLLabel",array(
			"html" => $entity->getTitle(),
			"soy2prefix" => "cms"
		));
		
		
		$this->createAdd("excerpt","HTMLLabel",array(
			"text" => mb_strimwidth(
					strip_tags($entity->getContent()),0,100,"...","UTF-8"
				),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("summary","HTMLLabel",array(
			"html" => ($this->summary >= 0) ?
						mb_strimwidth(strip_tags($entity->getContent()),0,$this->summary,"...","UTF-8")
					:	$entity->getContent(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("content","HTMLLabel",array(
			"html" => $entity->getContent(),
			"soy2prefix" => "cms"
		));
		
		/* 名前 */
		
		$this->createAdd("author","HTMLLabel",array(
			"text" => (strlen($entity->getAuthor()) > 0) ? $entity->getAuthor() :
			((isset($users[$entity->getAuthorId()]) ? $users[$entity->getAuthorId()]->getName() : "-")),
			"soy2prefix" => "cms"
		));
		
		list($author_link_text,$author_link_url) = $this->getAuthorLink($entity);
		
		$this->createAdd("author_link","HTMLLink",array(
			"link" => $author_link_url,
			"soy2prefix" => "cms"
		));
		

		$this->createAdd("author_link_text","HTMLLabel",array(
			"html" => $author_link_text,
			"soy2prefix" => "cms"
		));
		
		/* 作成日 */
		
		$this->createAdd("create_date","DateLabel",array(
			"text" => $entity->getCreateDate(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("update_date","DateLabel",array(
			"text" => $entity->getUpdateDate(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("entry_link","EntryLink",array(
			"link" => rawurldecode($entity->getUri()),
			"dir" => (isset($mappings[$entity->getDirectory()])) ? soycms_get_page_url($mappings[$entity->getDirectory()]["uri"]) : "",
			"soy2prefix" => "cms"
		));
		
		
		//entry_url_text
		$link = (isset($mappings[$entity->getDirectory()])) ? soycms_get_page_url($mappings[$entity->getDirectory()]["uri"],rawurldecode($entity->getUri())) : "";
		$link = preg_replace('/\/index\.html$/',"/",$link);
		
		$this->addLabel("entry_url",array(
			"text" => $link,
			"soy2prefix" => "cms"
		));
		
		$labels = $this->getLabels();
		$this->createAdd("label_list","SOYCMS_EntryLabelList",array(
			"soy2prefix" => "cms",
			"list" => $this->getEntryLabels($entity->getId()),
			"labels" => $labels,
			"uri" => (isset($mappings[$entity->getDirectory()])) ? $mappings[$entity->getDirectory()]["uri"] : ""
		));
		$this->addModel("has_label",array(
			"soy2prefix" => "cms",
			"visible" => count($labels) > 0
		));
		$this->addModel("no_label",array(
			"soy2prefix" => "cms",
			"visible" => count($labels) == 0
		));
		
		
		/* 記事編集リンク */
		$this->addModel("entry_edit_link_wrap",array(
			"soy2prefix" => "cms",
			"visible" => (defined("SOYCMS_EDIT_ENTRY") && SOYCMS_EDIT_ENTRY)
		));
		
		$this->addLink("entry_edit_link",array(
			"soy2prefix" => "cms",
			"link" => (defined("SOYCMS_ADMIN_ROOT_URL")) ? SOYCMS_ADMIN_ROOT_URL . "site/entry/detail/" . $entity->getId() : "",
			"visible" => (defined("SOYCMS_EDIT_ENTRY") && SOYCMS_EDIT_ENTRY)
		));
		
		/* 一覧の時だけ */
		if($this->mode != "detail"){
			$sections = $entity->getSectionsList();
			$html = "";
			$isMore = false;
			if(is_array($sections)){
				foreach($sections as $section){
					if($section instanceof SOYCMS_EntrySection_MoreSection){
						$isMore = true;
						break;
					}
					if($section instanceof SOYCMS_EntrySection_IntroductionSection){
						$isMore = true;
						break;
					}
					$html .= $section->getContent();
				}
			}
			$this->createAdd("introduction","HTMLLabel",array(
				"html" => $html,
				"soy2prefix" => "cms"
			));
			
			$this->createAdd("entry_more_link","HTMLLink",array(
				"link" => $link . "#more",
				"soy2prefix" => "cms",
				"visible" => $isMore
			));
			
			$this->addModel("entry_more_link_wrap",array(
				"visible" => $isMore,
				"soy2prefix" => "cms",
			));
		}
		
	}
	
	/**
	 * 記事のラベルを取得
	 */
	function getEntryLabels($entryId){
		return SOY2DAO::find("SOYCMS_EntryLabel",array(
			"entryId" => $entryId
		));
	}
	
	function getAuthorLink($entry){
		$text = $entry->getAttribute("author_link_text");
		$link = $entry->getAttribute("author_link_url");
		
		if(strlen($text)>0 && strlen($link)>0){
			return array($text,$link);
		}
		
		if(!isset($this->users[$entry->getAuthorId()])){
			return array("","");
		}
		
		$user = $this->users[$entry->getAuthorId()];
		$config = $user->getConfigArray();
		
		return array(
			$config["link_text"],
			$config["link_url"]
		);
		
	}
	
	function buildCustomFields($entity){
		static $commonConfig = array();
		
		if(!$commonConfig){
			$commonConfig = SOYCMS_ObjectCustomFieldConfig::loadConfig("common");
		}
		
		//個別設定
		if($this->getDirectoryUri()){
			$fields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry-" . $this->getDirectoryUri());
		}else{
			$fields = SOYCMS_ObjectCustomFieldConfig::loadObjectConfig("entry");
		}
		
		$components = $this->getComponentsList();
		
		//dummy
		if($entity->getId() == -1 || !$entity->getId()){
			$values = array();
			foreach($fields as $key => $config){
				
				//使わない奴はスキップする
				if(!in_array($key,$components)
				&& !in_array($key . "_sets",$components)
				&& !in_array($key . "_image",$components)
				&& !in_array($key . "_list",$components)){
					continue;
				}
				
				if(isset($values[$key])){
					$value = $values[$key];
				}else{
					$value = $config->getValueObject();
					if($config->isMulti() || $config->getType() == "check"){
						$value = array($value);
					}
				}
				SOYCMS_ObjectCustomFieldHelper::build($this,$config,$value);
			}
			return;
		}
		
		$values = SOYCMS_ObjectCustomField::getValues("entry",$entity->getId());
		
		foreach($values as $key => $value){
			if(!isset($fields[$key]))continue;
			
			//使わない奴はスキップする
			if(!in_array($key,$components)
			&& !in_array($key . "_sets",$components)
			&& !in_array($key . "_image",$components)
			&& !in_array($key . "_list",$components)){
				continue;
			}
			
			$config = $fields[$key];
			SOYCMS_ObjectCustomFieldHelper::build($this,$config,$value);
		}
	}
	
	
	/* getter setter */

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function setSummary($value){
		$this->summary = $value;
	}
	function getLink() {
		return $this->link;
	}
	function setLink($link) {
		$this->link = $link;
	}
	function getConfigLink() {
		return $this->configLink;
	}
	function setConfigLink($configLink) {
		$this->configLink = $configLink;
	}
	
	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
	
	

	public function getDirectoryUri(){
		return $this->directoryUri;
	}

	public function setDirectoryUri($directoryUri){
		$this->directoryUri = $directoryUri;
		return $this;
	}
}

class SOYCMS_EntryDetailComponent extends SOYCMS_EntryListComponent{
	
	private $directory = null;
	
	function populateItem($entity,$key){
		
		if(false == ($entity instanceof SOYCMS_Entry)){
			$entity = new SOYCMS_Entry();
			$entity->setDirectory($this->directory);
		}
		
		return parent::populateItem($entity,$key);
	
	}
	
	function getStartTag(){
		return '<?php SOYCMS_ItemWrapComponent::startTag("entry","'.$this->getId().'",null,"'.$this->getLink().'".$'.$this->getPageParam().'["'.$this->getId().'"][0]["entry_id"]); ?>' .
		 			HTMLList::getStartTag();
		
	}

	
	function getEndTag(){
		return HTMLList::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	

	function getDirectory() {
		return $this->directory;
	}
	function setDirectory($directory) {
		$this->directory = $directory;
	}
}

?>