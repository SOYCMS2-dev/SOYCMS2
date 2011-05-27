<?php
/**
 * @title ページ
 */
class page_page_index extends SOYCMS_WebPageBase{

	function page_page_index(){
		$this->jump("/page/list");
	}
}