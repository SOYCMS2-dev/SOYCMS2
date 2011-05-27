<?php

class SOYCMS_DynamicEditNaviComponent extends SOYBodyComponentBase{

	function execute(){
		
		$this->addLink("dynamic_edit_link",array(
			"link" => "?dynamic",
			"soy2prefix" => "cms" 
		));
		
		parent::execute();
	}
}
?>