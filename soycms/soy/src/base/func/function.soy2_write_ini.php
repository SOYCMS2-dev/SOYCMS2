<?php
define("SOY2_DOUBLE_QUOTE",'"');

function soy2_write_ini($filepath,$array){
	
	file_put_contents($filepath,soy2_ini_encode($array));
	
}

function soy2_ini_encode($array){
	$content = "";
	uasort($array,create_function('$a,$b','return (is_array($b)) ? -1 : 1;'));
	
	foreach($array as $key => $value){
		
		if($value instanceof stdClass)$value = (array)$value;
		
		
		if(is_array($value)){
			$content .= "\n";
			$content .= "[$key]";
			$content .= "\n";
			
			$content .= soy2_ini_encode($value);
			$content .= "\n";
		}else{
			//改行が含まれるデータ
			if(
				strpos($value,"\n")!==false
				|| strpos($value,"(")!==false
				|| strpos($value,")")!==false
				|| strpos($value,'"')!==false
			){
				$value = str_replace('"','" SOY2_DOUBLE_QUOTE "',$value);
				$value = '"' . $value . '"';
			}
			
			$content .= $key . " = " . $value;
			$content .= "\n";
		}
	}
	
	return $content . "\n";
}