<?php
SOY2::import("plugin.PluginInfo");

/**
 * 準備
 * 
 * PluginManager::load(ext,module) ... 
 */
class PluginManager extends SOY2Plugin{
	
	public static function getInstance(){
		static $_inst;
		
		if(!$_inst){
			$_inst = new PluginManager();
		}
		
		return $_inst;
	}
	
	
	/**
	 * 特定の拡張などを読み込む
	 */
	public static function load($extensionId,$id = null){
		
		if($id && strpos($id,".") !== false){
			$id = substr($id,0,strpos($id,"."));
		}
		
		$inst = self::getInstance();
		$inst->prepare(SOYCMS_SITE_DIRECTORY . ".plugin/");
		$inst->loadExtension($extensionId,$id);
	}
	
	function loadExtension($extensionId,$module){
		static $loaded = array();
		$extensionId = preg_replace('/\.\*$/',"",$extensionId);
		
		//delegetorの読み込み
		@SOY2::import("plugin.extensions.",$extensionId . ".php");
		
		if(in_array(array($extensionId, $module), $loaded)){
			return;
		}else{
			$loaded[] = array($extensionId, $module);
		}
		
		if(!$module || $module < 0){
			$plugins = $this->plugins;
			foreach($plugins as $plugin){
				if($plugin->isActive()){
					$plugin->load($extensionId);
				}
			}

		}else{
			$plugin = self::getPluginInfo($module);
			$plugin->load($extensionId);
		}
	}
	
	/**
	 * プラグインが配置されているディレクトリ
	 */
	function getPluginDirectory(){
		return SOYCMS_COMMON_DIR . "plugin/";
	}
	
	private function PluginManager(){
		
	}
	
	private $plugins = array();
	
	/**
	 * check
	 */
	function prepare($directory){
		
		$pluginDir = $this->getPluginDirectory();
		$files = @soy2_scandir($directory);
		
		foreach($files as $file){
			$id = str_replace(".active","",$file);
			if(file_exists($pluginDir . $id) && is_dir($pluginDir . $id)){
				$this->plugins[$id] = new PluginInfo($id,soy2_realpath($pluginDir . $id));
				$this->plugins[$id]->setIsActive(true);
			}else if(file_exists($directory . $id) && is_dir($directory . $id)){
				$this->plugins[$id] = new PluginInfo($id,soy2_realpath($directory . $id));
				$this->plugins[$id]->setIsActive(true);
			}
		}
		
		return $this->plugins;
	}
	
	public static function getPluginInfo($id){
		$pluginDir = self::getInstance()->getPluginDirectory();
		
		if(file_exists(SOYCMS_SITE_DIRECTORY . ".plugin/" . $id)){
			$info = new PluginInfo($id,soy2_realpath(SOYCMS_SITE_DIRECTORY . ".plugin/" . $id));
		}else{
			$info = new PluginInfo($id,soy2_realpath($pluginDir . $id));
		}
		
		$info->prepare();
		$info->checkActive(SOYCMS_SITE_DIRECTORY . ".plugin/");
		return $info;
	}
	
	/**
	 * 全てのプラグインを取得
	 */
	function listPlugins($type = null){
		$all = array();
		
		$pluginDir = $this->getPluginDirectory();
		$files = soy2_scandir($pluginDir);
		foreach($files as $file){
			if(is_dir($pluginDir . $file)){
				$id = basename($file);
				$all[$id] = $pluginDir . $file;
			}
		}
		
		$files = soy2_scandir(SOYCMS_SITE_DIRECTORY . ".plugin/");
		
		foreach($files as $file){
			if(is_dir(SOYCMS_SITE_DIRECTORY . ".plugin/" . $file)){
				$id = basename($file);
				$all[$id] = SOYCMS_SITE_DIRECTORY . ".plugin/" . $file;
			}
		}
		
		$plugins = array();
		foreach($all as $id => $directory){
			$info = new PluginInfo($id,soy2_realpath($directory));
			$info->checkActive(SOYCMS_SITE_DIRECTORY . ".plugin/");
				
			try{
			
				//種別指定も出来る
				if($type){
					$info->prepare();
					if(!$info->checkType($type)){
						continue;
					}
				}
				
			}catch(Exception $e){
				continue;
			}
			
			$plugins[$id] = $info;
		}
		
		return $plugins;
	}
	
	/* helper method */
	
	public static function import($pluginId,$path,$extension = ".class.php"){
		if(class_exists($path)){
			return $path;
		}
		if(strlen($pluginId)<1)throw new Exception("invalid plugin id:" . $pluginId);
		if($pluginId[0] == ".")throw new Exception("invalid plugin id:" . $pluginId);
		
		$tmp = array();
		preg_match('/\.([a-zA-Z0-9_]+$)/',$path,$tmp);
		if(count($tmp)){
			$className = $tmp[1];
		}else{
			$className = $path;
		}
		
		$dir = SOYCMS_COMMON_DIR . "plugin/" . $pluginId . "/";
		
		$path = str_replace(".","/",$path);
		$result = include_once($dir.$path.$extension);
		if($result == false){
			return false;
		}
		return $className;
	}
	
	/* getter setter */

	function getPlugins() {
		return $this->plugins;
	}
	function setPlugins($plugins) {
		$this->plugins = $plugins;
	}
}
?>