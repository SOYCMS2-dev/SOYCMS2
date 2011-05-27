<?php

class page_ajax_index extends SOYCMS_WebPageBase{
	
	private $id;
	
	function doPost(){
	}
	
	function init(){
	}
	
	function page_ajax_index($args) {
		$action = @$args[0];
		
		
		switch($action){
			case "update_help_status":
				$this->updateHelpStatus($_GET["id"],$_GET["status"]);
				break;
			case "get_help_status":
				$this->updateHelpStatus(null,null);
				break;
			case "update_sitemap_mode":
				SOYCMS_UserData::put("sitemap_mode",$_GET["mode"]);
				break;
			case "track":
				$this->updateUserTrack();
				break;
		}
		
		
		exit;
	}
	
	function updateHelpStatus($id,$value){
		$config = SOYCMS_UserData::get("help_status",array());
		if(!$config)$config = array();
		
		if($id){
			$config[$id] = $value;
			SOYCMS_UserData::put("help_status",$config);
			echo "saved" . var_export($value);
			exit;
		}
		
		echo json_encode($config);
	}
	
	/**
	 * ユーザの足跡を記録する
	 */
	function updateUserTrack(){
 		
 		$token = $_POST["token"];
 		$uri = $_POST["uri"];
 		$query = $_POST["query"];
 		$counter = $_POST["counter"];
		
		$obj = new SOYCMS_SiteUserActivity();
		$obj->setToken($token);
		$obj->setUri($uri);
		$obj->setQuery($query);
		$obj->save();
		
		$dao = $obj->getDAO();
		
		$time = ($counter == 0) ? 3 : 4;
		
		//ゴミ掃除
		//5秒以上前のデータは削除
		//5秒毎にアクセスされる
		$dao->deleteByTime(time() - $time);
		$activities = $dao->getByUri($uri);
		
		$res = array();
		foreach($activities as $tmp){
			$res[] = array(
				"name" => $tmp->getUserName(),
				"token" => $tmp->getToken()
			);
		}
		
		echo json_encode($res);
		exit;
 		   	
	}
}
?>