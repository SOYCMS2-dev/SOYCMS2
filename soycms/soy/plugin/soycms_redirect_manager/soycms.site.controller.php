<?php
class SOYCMS_RedirectManagerExtension implements SOY2PluginAction{
	
	function SOYCMS_RedirectManagerExtension(){
		include(dirname(__FILE__) . "/src/common.inc.php");
	}
	
	
	//prepare
	function prepare(){
		
		//URLを取得
		$uri = $_SERVER["REQUEST_URI"];
		
		$config = SOYCMS_RedirectManager::load();
		$agents = array();
		
		if(count($config)>0){
			$agents = $this->checkAegent();
		}
		
		foreach($config as $index => $array){
			$this->checkRule($index,$uri,$array,$agents);
		}
	}
	
	
	/**
	 * UserAgentをチェックする
	 */
	function checkAegent(){
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$res = array();
		
		$res["docomo"] = strpos($ua,"DoCoMo") !== false;
		$res["imode2.0"] = strpos($ua,"DoCoMo/2.0") !== false; 
		
		$res["au"] = 	strpos($ua,"KDDI") !== false || 
						strpos($ua,"UP.Browser") !== false;
		
		$res["SoftBank"] = strpos($ua,"SoftBank") !== false;
		
		$res["Willcom"] = strpos($ua,"WILLCOM") !== false;
		
		$res["smartphone"] = 
					strpos($ua,'iPhone') !== false ||          // Apple iPhone
					strpos($ua,'iPod') !== false ||            // Apple iPod touch
				    strpos($ua,'Android') !== false ||         // 1.5+ Android
				    strpos($ua,'dream') !== false ||           // Pre 1.5 Android
				    strpos($ua,'CUPCAKE') !== false ||         // 1.5+ Android
				    strpos($ua,'blackberry') !== false ||  		// Storm
				    strpos($ua,'webOS') !== false ||           // Palm Pre Experimental
				    strpos($ua,'incognito') !== false ||       // Other iPhone browser
				    strpos($ua,'webmate') !== false;          // Other iPhone browser
		
		$res["iPhone"] = strpos($ua,'iPhone') !== false;
		$res["iPad"] = strpos($ua,'iPad') !== false;
		$res["Android"] = strpos($ua,'Android') !== false;
		
		$res["mobile"] = (in_array(true,$res));
		$res["pc"] = (!in_array(true,$res));
		
		//PC
		$res["IE"] = strpos($ua,'MSIE') !== false;
		$res["IE6"] = strpos($ua,'MSIE 6') !== false;
		$res["Opera"] = strpos($ua,'Opera') !== false;
		$res["Safari"] = strpos($ua,'Safari') !== false;
		$res["Chrome"] = strpos($ua,'Chrome') !== false;
		$res["FF"] = strpos($ua,'Firefox') !== false;
		
		return $res;
	}
	
	/**
	 * チェックする
	 */
	function checkRule($index,$uri,$array,$agent){
		
		$regex = array();
		
		$rule = $array["url"];
		if(preg_match('/^https?:/',$rule)){
			$rule = preg_replace('/^https?:\/\/[^\/]+(\/.*)/','$1',$rule);
		}
		
		if(preg_match('/^https?:/',$array["to"])){
			$array["to"] = preg_replace('/^https?:\/\/[^\/]+(\/.*)/','$1',$array["to"]);
		}
		
		
		//urlでチェック
		if(@$array["regex"]){
			$rule = "/" . str_replace("/","\/",$rule) . "/";
			if(!preg_match($rule,$uri,$regex)){
				return;
			}
		}else{
			if($rule != $uri){
				return;
			}
		}
		
		//user agentでチェック
		$agents = @$array["agent"];
		if($agents){
			$flag = false;
			foreach($agents as $value){
				$flag = @$agent[$value];
				if($flag){
					break;
				}
			}
			
			if(!$flag){
				return;
			}
		}
		
		//一回だけの時はCookieをチェック
		if(@$array["count"] == "once"){
			$indexs = array();
			if(isset($_COOKIE["soycms_redirect_once"])){
				$indexs = explode(",",$_COOKIE["soycms_redirect_once"]);
				if(in_array($index,$indexs)){
					return;
				}
			}
			$indexs[] = $index;
			$indexs = array_unique($indexs);
			
			$path = "/" . array_shift(explode("/",$uri));
			setcookie("soycms_redirect_once",implode(",",$indexs),time() + 60*60*24*30, $path);
		}
		
		//遷移実行
		$to = SOY2PageController::createRelativeLink(@$array["to"],true);
		
		//replace $0....$N
		foreach($regex as $key => $value){
			$to = str_replace('$' . $key, $value, $to);
		}
		
		if(strlen($to)>0){
			
			switch($array["code"]){
				case "301":
					header( "HTTP/1.0 301 Moved Permanently" );
					break;
				case "302":
					header( "HTTP/1.0 302 Moved Temporarily" );
					break;
				case "307":
					header( "HTTP/1.1 307 Temporary Redirect" );
					break;
				default:
					header($array["code"]);
					break;
			}
			
			header("Location: " . $to);
			exit;
		}
	}
	

}

PluginManager::extension("soycms.site.controller.prepare","soycms_redirect_manager","SOYCMS_RedirectManagerExtension");

