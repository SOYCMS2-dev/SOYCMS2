<?php
function soycms_print_menu($url,$label,$rule = null,$child = array()){
	
	if($url[0] == "/"){
		
	}else{
		$url = SOYCMS_ROOT_URL . $url;
	}
	
	$req = $_SERVER["REQUEST_URI"];
	if(strpos($req,"?") !== false)$req = substr($req,0,strpos($req,"?"));
	
	$flag = false;
	
	if(!$rule){
		$rule = '/('. str_replace("/","\\/",$url) . ')$/';
	}else{
		$rule = '/('. str_replace("/","\\/",$rule) . ')$/';		
	}
	
	$isOn = false;
	if(preg_match($rule,$req)){
		$flag = true;
		$isOn = true;
	}
	
	
	ob_start();
	if(count($child) > 0){
		if($flag){
			echo "<ul>";
		}else{
			echo "<ul style=\"display:none;\">";
		}
		foreach($child as $array){
			$_isOn = call_user_func_array("soycms_print_menu",$array);
			if($_isOn){
				$isOn = false;
			}
		}
		echo "</ul>";
	}
	$child = ob_get_contents();
	ob_end_clean();
	
	echo (($isOn) ? "<li class='on'>" : "<li>"),
		"<a href=\"${url}\">${label}</a>";
	echo $child;
	
	echo "</li>\n";
	
	return $isOn;
}

function soycms_print_menu_head($url,$label){
	if($url[0] == "/"){
	
	}else{
		$url = SOYCMS_ROOT_URL . $url;
	}
	
	echo "<a href=\"${url}\">${label}</a>";
}