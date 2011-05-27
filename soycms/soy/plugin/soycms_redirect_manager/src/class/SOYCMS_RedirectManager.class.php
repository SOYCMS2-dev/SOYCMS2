<?php

class SOYCMS_RedirectManager {

	/**
	 * 設定値取得
	 */
    public static function load(){
    	return SOYCMS_DataSets::get("soycms_redirect_manager.config",array());
    }
    
    /**
     * 保存
     */
    public static function save($config){
    	return SOYCMS_DataSets::put("soycms_redirect_manager.config",$config);
    }
}
?>