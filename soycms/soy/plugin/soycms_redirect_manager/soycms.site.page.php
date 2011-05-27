<?php
class SOYCMS_RedirectManagerConfigPage extends SOYCMS_SitePageExtension{
	
	function SOYCMS_RedirectManagerConfigPage(){
		include(dirname(__FILE__) . "/src/common.inc.php");
		
	}
	
	/**
	 * @return string
	 */
	function getTitle(){
		return "リダイレクトの設定";
	}
	
	function getConfig(){
		return SOYCMS_RedirectManager::load();
	}
	
	function saveConfig($config){
		SOYCMS_RedirectManager::save($config);
	}
	
	function doPost(){
		
		$config = $this->getConfig();
		
		if(isset($_POST["new"]) && isset($_POST["NewRule"])){
			if(strlen($_POST["NewRule"]["name"])<1)$_POST["NewRule"]["name"] = "新しいルール";
			$config[] = $_POST["NewRule"];
			$this->saveConfig($config);
		}
		
		if(isset($_POST["new_list"]) && isset($_POST["NewRules"])){
			$lines = explode("\n",$_POST["NewRules"]);
			if(isset($_POST["clear_config"])){
				$config = array();
			}
			foreach($lines as $key => $line){
				$array = explode(",",$line);
				
				if(strlen($array[0])<1){
					$array[0] = "New Rule #" . ($key+1);
				}
				
				if(strlen(@$array[1]) < 1 || strlen(@$array[2]) < 1){
					continue;
				}
				
				if(strlen(@$array[4])<1)$array[4] = "301";
				
				$conf = array(
					"name" => $array[0],
					"url" => $array[1],
					"to" => $array[2],
					"code" => $array[4],
					"count" => ""
				);
				
				$config[] = $conf;
			}
			
			$this->saveConfig($config);
		}
		
		if(isset($_POST["save"]) && isset($_POST["Rule"])){
			$ids = array_keys($_POST["save"]);
			foreach($ids as $id){
				$_config = $_POST["Rule"][$id];
				$config[$id] = $_config;
			}
			$this->saveConfig($config);
		}
		
		if(isset($_POST["remove"])){
			$ids = array_keys($_POST["remove"]);
			foreach($ids as $id){
				unset($config[$id]);
			}
			$config = array_values($config);
			$this->saveConfig($config);
		}
		
		if(isset($_POST["save_order"])){
			$config = array_values($_POST["Rule"]);
			$this->saveConfig($config);
		}
		
		
		SOY2PageController::redirect(soycms_create_link("/ext/soycms_redirect_manager") . "?updated");
	}
	
	/**
	 * @return string
	 */
	function getPage(){
		$config_array = $this->getConfig();
		
		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function printUserAgentCheck($name,$values = null){
		if(!$values){
			$values = array();
		}
		
		$array = array(
			"基本" => array(
				"mobile" => "Mobileのみ",
				"pc" => "PCのみ"
			),
			"mobile" => array(
				"docomo" => "docomo",
				"imode2.0" => "i-mode2.0",
				"au" => "au",
				"SoftBank" => "SoftBank",
				"Willcom" => "Willcom",
				"smartphone" => "Smartphone",
				"iPhone" => "iPhone",
				"iPad" => "iPad",
				"Android" => "Android",
			),
			"PC" => array(
				/* "Tablet PC" => "Tablet", */
				"IE" => "Internet Explorer",
				"IE6" => "Internet Explorer6系",
				"Opera" => "Opera",
				"Safari" => "Safari",
				"Chrome" => "Chrome",
				"FF" => "Firefox",
			)
		);
		
		foreach($array as $label => $_array){
			echo "<div>";
			echo "<span><strong>"  . $label . "</strong> | </span>";
			foreach($_array as $key => $value){
				$checked = (in_array($key,$values)) ? "checked" : "";
				echo '<input type="checkbox" name="'.$name.'[]" value="'.$key.'" id="'.$key.'_check" '.$checked.'/>';
				echo '<label for="'.$key.'_check">'.$value.'</label>';
			}
			echo "</div>";
		}
	}
}
PluginManager::extension("soycms.site.page","soycms_redirect_manager","SOYCMS_RedirectManagerConfigPage");
