<?php

class page_init_index extends WebPage{

	function doPost(){
		session_start();
		
		$mode = @$_POST["mode"];
		$notify = @$_POST["notify"];
		
		if($mode == "check_config_dir"){
			$configDir = @$_POST["config_dir"];
			if(!file_exists($configDir)){
				soy2_mkdir($configDir);
			}
			
			echo '<script type="text/javascript">';
			
			if(is_writable($configDir)){
				$res = 1;
			}else{
				$res = -1;
			}
			
			if($notify)echo 'window.parent.' . $notify . '("'.htmlspecialchars($res,ENT_QUOTES).'")'; 
			echo '</script>';
			
			exit;
		}
		
		if($mode == "init"){
			
			try{
				//sqlite版で作成
				$initLogic = SOY2Logic::createInstance("admin.logic.init.InitLogic");
				$res = (int)$initLogic->init();
				
				echo '<script type="text/javascript">';
				if($notify)echo 'window.parent.' . $notify . '("'.htmlspecialchars($res,ENT_QUOTES).'")'; 
				echo '</script>';
			}catch(Exception $e){
				echo '<script type="text/javascript">';
				echo 'alert("初期化に失敗しました。'.htmlspecialchars($e->getMessage(),ENT_QUOTES).'");';
				echo '</script>';
			}
			
			exit;
			
		}

		exit;

	}

	function page_init_index(){
		
		if(isset($_GET["inited"])){
			SOY2PageController::redirect("./");
		}
		
		//既にあれば初期化はさせない
		if(file_exists(SOYCMSConfigUtil::get("config_dir") . "db.conf.php")){
			SOY2PageController::redirect("./");
		}
		
		WebPage::WebPage();
		
		$this->createAdd("init_form","HTMLForm");
		
		$dir = dirname($_SERVER["DOCUMENT_ROOT"]);
		if(is_writable($dir)){
			$dir .= "/soycms_config";
		}
		
		$this->createAdd("config_dir","HTMLInput",array(
			"name" => "config_dir",
			"value" => $dir
		));
		
		$this->createAdd("user_id","HTMLInput",array(
			"name" => "User[userId]",
			"value" => ""
		));
		
		$this->createAdd("password","HTMLInput",array(
			"name" => "User[password]",
			"value" => ""
		));
		
		$this->createAdd("password_confirm","HTMLInput",array(
			"name" => "password_confirm",
			"value" => ""
		));
		
	}
	
	function getLayout(){
		return "login.php";
	}
}
?>