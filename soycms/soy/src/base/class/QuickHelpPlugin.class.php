<?php

class QuickHelpPlugin extends HTMLPluginBase{
	
	public static function getHelpStatus(){
		static $config = null;
		
		if(is_null($config)){
			$config = SOYCMS_UserData::get("help_status",array());
		}
		
		return $config;
	}
	
	function executePlugin($soyValue){
		$closed = $this->getAttribute("help:closed");
		if($closed){
			$closed = "help-closed";
		}else{
			$closed = "";
		}
		
		$html = <<<HTML
<div class="help-window"> 
	<dl class="help-inner $closed" id="soycms-help-{$soyValue}"> 
		<dt class="help-btn-wrap">
			<strong class="help-btn" title="ヘルプを開く" onclick="common_show_help(this,'{$soyValue}');"><span title="ヘルプを開く"></span></strong>
			<strong class="help-close-btn" title="ヘルプを閉じる" onclick="common_update_help_status('{$soyValue}',false);"></strong> 
		</dt>
	</dl> 
</div>
HTML;
		
		$this->setInnerHTML($html);
	}
}
?>