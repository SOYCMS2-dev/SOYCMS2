<?php
function soy2_mkdir($path){
	umask(0);
	return mkdir($path,0755,true);
}
?>