<?php
function soycms_union_uri(){
	
	$arguments = func_get_args();
	$isQuery = false;
	
	$tmp = array();
	foreach($arguments as $key => $value){
		if($key == 0 && $value == "_home"){
			continue;
		}
		
		//先頭の場合は末尾の/だけ
		if($key == 0){
			$value = preg_replace('/\/$/',"",$value);
			if(strlen($value)<1){
				$tmp[] = $value;
				continue;
			}
		
		//先頭と末尾の/は取り除く
		}else{
			$value = preg_replace('/^\/|\/$/',"",$value);
		}
		if(strlen($value)<1)continue;
		if(!$isQuery && strpos($value,"?")!==false)$isQuery = true;
		$tmp[] = $value;
	}
	
	$last = $arguments[count($arguments)-1];
	if(strpos($last,".")===false && !$isQuery){
		$tmp[] = "";
	}
	
	return implode("/",$tmp);
	
}
?>
