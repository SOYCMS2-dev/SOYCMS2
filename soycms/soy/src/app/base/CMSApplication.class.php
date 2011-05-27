<?php

class CMSApplication {

	public static function run(){
		
		//pathinfoからアプリケーションIDを取得
		$pathinfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "";
		
		if(strlen($pathinfo)<1){
			SOY2PageController::redirect("../admin/");
			exit;
		}
		
		$paths = array_values(array_diff(explode("/",$pathinfo),array("")));
		if(count($paths)<1){
			SOY2PageController::redirect("../admin/");
			exit;
		}
		$applicationId = $paths[0];
		$arguments = array_slice($paths,1);
		$inst = self::getInstance($applicationId,$arguments);
		$inst->execute($applicationId,$arguments);
	}
	
	public static function getInstance($applicationId = null,$arguments = null){
		static $_inst;
		if(!$_inst){
			$_inst = new CMSApplication();
			$_inst->init($applicationId,$arguments);
		}
		
		return $_inst;
	}
	
	/**
	 * メイン関数の登録
	 */
	public static function main($func,$method = null){
		$obj = CMSApplication::getInstance();
		if($method){
			$func = array($func,$method);
		}
		$obj->appMain = $func;
	}
	
	public static function setTabs($tabs){
		$obj = CMSApplication::getInstance();
		$obj->tabs = $tabs;
	}

	/**
	 * 有効なタブを設定します
	 */
	public static function setActiveTab($id){
		$obj = CMSApplication::getInstance();
		$obj->activeTab = $id;
	}
	
	/**
	 * レイアウトを設定します
	 */
	public static function layout($layout){
		$obj = CMSApplication::getInstance();
		$obj->layout = $layout;
	}
	
	/**
	 * URLを作成
	 */
	public static function createLink($path = null){
		$self = CMSApplication::getInstance();
		$path = str_replace(".","/",$path);
		$path = $self->applicationId ."/" .$path;
		return soycms_create_link($path);
	} 
	
	public static function addLink($url){
		
	}
	
	/* */
	
	private $applicationId;
	private $arguments = array();
	private $appMain;
	private $properties;
	private $tabs = array();
	private $activeTab;
	private $layout = "app";
	
	function init($applicationId,$arguments){
		
		$this->applicationId = $applicationId;
		$this->arguments = $arguments;
		
		//設定ファイルの読み込み
		$this->properties = (file_exists(SOYCMS_APP_ROOT . $applicationId . "/application.ini")) ? parse_ini_file(SOYCMS_APP_ROOT . $applicationId . "/application.ini") : array();
		include_once(SOYCMS_APP_ROOT . $applicationId . "/admin.php");
		
	}
	
	function execute($applicationId,$arguments){
   		$html = "";
   		
   		if($this->appMain){
   			$html = call_user_func_array($this->appMain,array($applicationId,$this->arguments));
   		}
   		
   		$app = $this;
   		SOY2::RootDir(SOYCMS_COMMON_DIR . "src/");
   		SOY2HTMLConfig::LayoutDir(SOYCMS_LAYOUT_DIR);
   		include(SOYCMS_LAYOUT_DIR . "/" . $this->layout . ".php");
	}
	
	function getProperty($key){
		return (isset($this->properties[$key])) ? $this->properties[$key] : "----------"; 
	}
	
	 /**
	 * タブを表示
	 */
	function printTabs(){
		$tabs = $this->tabs;

		$html = "";
		$isActive = (is_null($this->activeTab)) ? true : false;
		foreach($tabs as $key => $tab){
			if(!is_numeric($key)){
				$id = $key;
			}else{
				$id = $this->applicationId . "_tab_" . $key;
			}

			if(isset($tab["label"]) && strlen($tab["label"]) > 0){
				$label = $tab["label"];
			}else{
				continue;
			}

			$href = (isset($tab["href"])) ? ' href="'.htmlspecialchars($tab["href"],ENT_QUOTES).'" ' : ' href="javascript:void(0);" ';
			$onclick = (isset($tab["onclick"])) ? ' onclick="'.htmlspecialchars($tab["onclick"],ENT_QUOTES) . '" ' : "";

			$className = "";
			if($isActive){
				$className .= "on";
				$isActive = false;
			}else if($key == $this->activeTab){
				$className .= "on";
			}
			
			$html .= 	'<li class="'.$className.'" id="'.$id.'">' .
						'<a '.$href.$onclick.'>'.$label.'</a></li>' .
						'</li>';
		}

		echo $html;
	}
	
	function printMenus(){
		if(file_exists(SOYCMS_APP_ROOT . $this->applicationId . "/menu.php")){
			$menuTitle = (isset($this->properties["menu-title"]))  ? $this->getProperty("menu-title") : $this->getProperty("name");
			echo '<div id="app-menu" class="section">';
			echo '<div class="title">';
			echo '<h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$menuTitle.'</h2>';
			echo '<p class="btn"><img src="'.SOYCMS_COMMON_URL.'img/cms-menu-title-btn.gif" alt="パネルを閉じる" width="12" height="12" /></p>';
			echo '</div>';
			echo '<div class="content">';
			echo '<ul>';
			include(SOYCMS_APP_ROOT . $this->applicationId . "/menu.php");
			echo '</ul>';
			echo '</div>';
			echo '</div>';
			echo '<!--  // #app-menu -->';
		}
	}
	
	/* getter setter */

	function getApplicationId() {
		return $this->applicationId;
	}
	function setApplicationId($applicationId) {
		$this->applicationId = $applicationId;
	}
	function getArguments() {
		return $this->arguments;
	}
	function setArguments($arguments) {
		$this->arguments = $arguments;
	}
	function getAppMain() {
		return $this->appMain;
	}
	function setAppMain($appMain) {
		$this->appMain = $appMain;
	}
	function getProperties() {
		return $this->properties;
	}
	function setProperties($properties) {
		$this->properties = $properties;
	}

	function getTabs() {
		return $this->tabs;
	}
}
?>