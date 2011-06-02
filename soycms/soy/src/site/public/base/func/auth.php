<?php
function soycms_basic_auth($id,$pass){

	@session_start();
	$error = "";
	$hash = md5($id . "--soycms--" . $pass);

	if(isset($_POST["php_auth_user"]) && isset($_POST["php_auth_pass"])){
		if($_POST["php_auth_user"] == $id
		&& $_POST["php_auth_pass"] == $pass){
			if(!isset($_SESSION["php_basic_auth"]))$_SESSION["php_basic_auth"] = array();
			$_SESSION["php_basic_auth"][] = $hash;
			$_SESSION["php_basic_auth"] = array_unique($_SESSION["php_basic_auth"]);
		}
		$error = '<p style="color:red;">ユーザIDまたはパスワードが違います。</p>';
	}

	$array = @$_SESSION["php_basic_auth"];
	if(!is_array($array))$array = array();
	$val = in_array($hash,$array);

	//ログインしていない時
	if(!$val){
		@header("HTTP/1.1 401 Unauthorized");
		echo <<<HTML
		<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>ユーザー認証</title>
		</head>
		<body>
		<div style="text-align:center;">
		<p>このページを表示するにはユーザー認証を行う必要があります。</p>
		{$error}
		<form method='post'>
		<p>Please input id/password</p>
		<p>ユーザーID:<input name='php_auth_user' style="width: 220px;" /></p>
		<p>パスワード:<input name='php_auth_pass' type="password" style="width: 220px;" /></p>
		<p><input type="submit" value="ログイン" /></p>
		</div>
		</form>
		</body>
		</html>
HTML;
		exit;
	}
}
