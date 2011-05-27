<?php
/**
 * SOYCMS便利クラス
 */
class SOYCMS_Helper {
	
	public static function set($key,$value){
		$inst = SOYCMS_Helper::inst();
		
		$inst->attributes[$key] = $value;
		if(is_null($value)){
			unset($inst->attributes[$key]);
		}
	}
	
	public static function get($key){
		$inst = SOYCMS_Helper::inst();
		
		return (isset($inst->attributes[$key])) ? $inst->attributes[$key] : null;
	}
	
	private static function inst(){
		static $inst;
		if(!$inst)$inst = new SOYCMS_Helper();
		return $inst;
	}
	
	private function SOYCMS_Helper(){
		//do nothing
	}
	
	private $attributes = array();
}
?>