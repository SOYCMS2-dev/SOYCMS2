<?php

class page_check extends WebPage{

    function page_check() {
    	
    	//DocumentRootにインストールされている
    	if(!isset($_SERVER["OLD_DOCUMENT_ROOT"])){
    		echo 0;
    		exit;
    	}
    	
    	echo (strcmp($_SERVER["DOCUMENT_ROOT"],$_SERVER["OLD_DOCUMENT_ROOT"]) === 0) ? 0 : 1;
    	exit;
    }
}
?>