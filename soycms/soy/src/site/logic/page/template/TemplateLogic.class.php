<?php

class TemplateLogic extends SOY2LogicBase{
	
	function createTemplate($templateId,$name,$html,$type){
		
		$template = new SOYCMS_Template();
		$template->setId($templateId);
		$template->setType($type);
		$template->setName($name);
		$template->setTemplate($html);
		$template->save();
		
		$logic = SOY2Logic::createInstance("site.logic.page.template.TemplateEditHelper");
		$logic->autoAppend($template);
		$logic->updateItemLayout($template);
		$template->save();
		
		return $template->getId();
	}
	

}
?>