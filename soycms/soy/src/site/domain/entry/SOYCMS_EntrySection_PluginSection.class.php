<?php

class SOYCMS_EntrySection_PluginSection extends SOYCMS_EntrySection{
	
	function build(){
		$values = $this->getValue();
		parse_str($values,$values);
		
		PluginManager::load("soycms.site.entry.section",$values["PLUGIN"]);
		$content = PluginManager::invoke("soycms.site.entry.section",array(
			"mode" => "build",
			"module" => $values["PLUGIN"]
		))->getContent($values["ARGUMENT"]);
		
		$this->setContent($content);
	}
	
	/**
	 * 毎回生成するため、buildを呼びます。
	 */
	function getContent(){
		$this->build();
		return parent::getContent();
	}
	
	function buildForm($html,$values){
		$options = array();
		
		//プラグイン実行
		//カスタムフィールド(自動保存では保存しないようにする)
		PluginManager::load("soycms.site.entry.section");
		$list = PluginManager::invoke("soycms.site.entry.section",array(
			"mode" => "list"
		))->getList();
		
		foreach($list as $key => $array){
			$value = $array["name"];
			$options[] = "<option value=\"{$key}\">{$value}</option>";
		}
		
		$options =  implode("",$options);

		$html = str_replace("#plugin_options#",$options,$html);
		return $html;
	}

}
?>