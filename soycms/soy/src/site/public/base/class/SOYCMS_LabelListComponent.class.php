<?php

class SOYCMS_LabelListComponent extends HTMLList{
	
	private $mode = "list";
	private $dirUrl = null;
	private $labelId = null;
	private $buildEntryList = true;
	
	function getStartTag(){
		if($this->mode == "list")return parent::getStartTag();
		return '<?php SOYCMS_ItemWrapComponent::startTag("label","'.$this->getId().'","'.$this->getLabelId().'"); ?>' .
		 			parent::getStartTag();
		
	}

	
	function getEndTag(){
		if($this->mode == "list")return parent::getEndTag();
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
	
	function populateItem($entity,$key){
		
		//custom field
		$this->buildCustomField($entity);
		
		$this->addLabel("label_name",array(
			"text" => $entity->getName(),
			"soy2prefix" => "cms"
		));
		
		$this->addLink("directory_label_link",array(
			"link" => soycms_get_page_url($this->dirUrl, rawurlencode($entity->getAlias())),
			"soy2prefix" => "cms"
		));
		
		if(!$this->buildEntryList)return;
		
		$entries = $entity->getEntries();
		
		//entry_list
		$this->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => $entries,
			"soy2prefix" => "cms",
			"mode" => "block"
		));
	}
	
	function buildCustomField($label){
		
		$id = ($label instanceof SOYCMS_Label) ? $label->getId() : 0;
		
		$configs = SOYCMS_ObjectCustomFieldConfig::loadConfig("label");
		$values = SOYCMS_ObjectCustomField::getValues("label",$id);
		
		foreach($configs as $key => $config){
			$value = (isset($values[$key])) ? $values[$key] : $config->getValueObject();
			if(is_array($value))$value = array_shift($value);
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

	function getDirUrl() {
		return $this->dirUrl;
	}
	function setDirUrl($dirUrl) {
		$this->dirUrl = $dirUrl;
	}

	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}

	function getBuildEntryList() {
		return $this->buildEntryList;
	}
	function setBuildEntryList($buildEntryList) {
		$this->buildEntryList = $buildEntryList;
	}
}
?>