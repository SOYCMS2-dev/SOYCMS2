<?php

class page_remind_password_error extends WebPage{

	function page_remind_password_error() {
		WebPage::WebPage();
	}
	
	function getLayout(){
		return "login.php";
	}
}
?>