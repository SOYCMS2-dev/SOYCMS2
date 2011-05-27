<?php

class SOYCMS_HTMLPager extends HTMLPager{

	function getStartTag(){
		return '<?php SOYCMS_ItemWrapComponent::startTag("pager","pager"); ?>' .
		 			parent::getStartTag();
		
	}

	
	function getEndTag(){
		return parent::getEndTag() .
			'<?php SOYCMS_ItemWrapComponent::endTag(); ?>';
	}
	
}
?>