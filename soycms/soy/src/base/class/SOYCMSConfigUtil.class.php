<?php
/**
 * SOY CMSの設定を行います
 */
class SOYCMSConfigUtil {

	private $propertyList = array();

	/**
	 * 各種設定の初期化
	 * 開発時はここを修正する
	 */
	private function init(){

		//ディレクトリ周りの設定
		$this->propertyList["config_dir"] = SOYCMS_ROOT_DIR . "conf/";
		$this->propertyList["db_dir"] = SOYCMS_ROOT_DIR . "db/";
		$this->propertyList["default-user-login"] = true;

	}

	/* 以下、public static */

	public static function get($key){
		return SOYCMSConfigUtil::getInstance()->getProperty($key);
	}
	public static function put($key,$value){
		return SOYCMSConfigUtil::getInstance()->setProperty($key,$value);
	}
	
	public static function loadConfig($file){
		$file = self::get("config_dir") . $file;
		return soy2_require($file);
	}


	private static function getInstance(){
		static $_static;
		if(!$_static){
			$_static = new SOYCMSConfigUtil();
			$_static->init();
		}
		return $_static;
	}

	private function getProperty($key){
		return (isset($this->propertyList[$key])) ? $this->propertyList[$key] : null;
	}
	
	private function setProperty($key,$value){
		$this->propertyList[$key] = $value;
	}

}
?>