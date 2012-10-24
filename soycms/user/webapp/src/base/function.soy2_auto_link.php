<?php
if(function_exists("soy2_auto_link"))return;

function soy2_auto_link($str){
	$patterns = array("/(https?|ftp)(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i");
	return preg_replace_callback($patterns,create_function('$array','$inner = $array[0];' .
			'$max = 60;' .
			'if(strlen($inner) > $max){' .
			'$inner = substr($inner,0,$max-3) . "...";' .
			'}' .
			'return \'<a href="\'.$array[0].\'">\'.$inner.\'</a>\';'
		),$str);
}

?>
