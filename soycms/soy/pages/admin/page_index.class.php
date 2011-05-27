<?php
/**
 * @title ログイン前トップ
 */
class page_index extends SOYCMS_WebPageBase{

	function page_index() {
		//siteのトップに移動する
		SOY2FancyURIController::redirect("../site/");
	}
}
?>