<?php
class SOYCMS_SitePublishConfigPage extends SOYCMS_SitePageExtension{
	
	function SOYCMS_SitePublishConfigPage(){
		include_once(dirname(__FILE__) . "/src/common.inc.php");
		
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "サイトの書き出し";
	}
	
	function doPost(){
		//設定の保存
		SOYCMS_DataSets::put("soycms_site_publish",array(
			"directory" => $_POST["output_dir"],
			"url" => $_POST["output_url"],
			"ftp" => $_POST["ftp"]
		));
		
		
		if(isset($_POST["test"])){
			$this->checkConnect($_POST["ftp"]);
		}
		
		if(isset($_POST["submit"])){
			$config = SOYCMS_DataSets::get("soycms_site_publish");
			
			//書き出し実行
			$target = dirname(__FILE__) . "/output.php";
			$arguments = array(
				SOYCMS_ROOT_DIR,
				SOYCMS_LOGIN_SITE_ID,
				SOYCMS_SITE_DIRECTORY,
				SOYCMS_SITE_URL,
				$config["directory"],
				$config["url"]
			);
			$arg = array();
			foreach($arguments as $_arg){
				$arg[] = '"'.$_arg.'"';
			}
			$arg = implode(" ",$arg);
			
			//非同期実行
			exec("php " . $target . " " . $arg . " > /dev/null &");
			
			//同期実行
			//system("php " . $target . " " . $arg);exit;
			
		}
		
		if(isset($_POST["upload"])){
			$target = dirname(__FILE__) . "/upload.php";
			$arguments = array(
				SOYCMS_ROOT_DIR,
				SOYCMS_LOGIN_SITE_ID,
			);
			$arg = array();
			foreach($arguments as $_arg){
				$arg[] = '"'.$_arg.'"';
			}
			$arg = implode(" ",$arg);
			
			//非同期実行
			exec("php " . $target . " " . $arg . " > /dev/null &");
			
			//同期実行
			//system("php " . $target . " " . $arg);exit;
		}
		
		$this->redirect("updated");
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		$config = SOYCMS_DataSets::get("soycms_site_publish",array(
			"directory" => SOYCMS_SITE_DIRECTORY . "export/",
			"url" => SOYCMS_SITE_URL,
			"ftp" => array(
				"port" => "21",
				"secure" => false
			)
		));
		$output_dir = @$config["directory"];
		$output_url = @$config["url"];
		$ftp = @$config["ftp"];
		if(!is_array(@$ftp["option"]))$ftp["option"] = array();
		
		$connect = SOYCMS_DataSets::get("soycms_site_publish.result", -1);
		
		ob_start();
		include(dirname(__FILE__) . "/src/form.php");
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	/**
	 * 接続テスト
	 */
	function checkConnect(){
		$res = SitePublishPlugin_FTPHelper::connect(
			$_POST["ftp"]["host"],
			$_POST["ftp"]["port"],
			$_POST["ftp"]["id"],
			$_POST["ftp"]["password"],
			$_POST["ftp"]["secure"]
		);
		SitePublishPlugin_FTPHelper::close($res);
		
		$result = ($res) ? date("Y-m-d H:i:s") : false;
		SOYCMS_DataSets::put("soycms_site_publish.result", $result);
		
		
		
	}
}
PluginManager::extension("soycms.site.page","soycms_site_publish","SOYCMS_SitePublishConfigPage");
