<?php

class CustomFieldConfigSelect extends HTMLSelect{
	
	private $mode;
	
	function init(){
		$options = array(
			0 => "使用しない",
			1 => "管理側のみ表示",
			10 => "ユーザ入力(任意)",
			20 => "ユーザ入力(必須)"
		);
		if($this->mode == "register"){
			unset($options[1]);
		}
		if($this->mode == "common"){
			$options[2] = "管理側のみ表示(CSV非表示)";
			ksort($options);
		}
		$this->setOptions($options);
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}

}
?>