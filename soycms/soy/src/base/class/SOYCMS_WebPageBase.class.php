<?php

class SOYCMS_HTMLPageBase extends WebPage{
	
	/**
	 * jump to the page
	 */
	function jump($path,$suffix = null){
		if(isset($_GET["layer"])){
			if(strpos($path,"?") === false){
				$path .= "?layer";
			}else{
				$path .= "&layer";
			}
		}
		
		SOY2FancyURIController::jump($path,$suffix);
	}
	
	/**
	 * reload current page
	 */
	function reload(){
		SOY2FancyURIController::reload();
	}
	
	/**
	 * SOY2ActionFactory::createInstanceのエイリアス
	 * 
	 * @return SOY2ActionResult
	 */
	function run($actionName,$array = array()){
		return SOY2ActionFactory::createInstance($actionName,$array)->run();
	}
	
	/**
	 * main
	 */
	function main(){
		//default do nothing
	}
	
	/**
	 * Overwrite display
	 */
	function display(){
		
		$this->main();
		
		ob_start();
		parent::display();
		$html = ob_get_contents();
		ob_end_clean();
		
		echo $html;
	}
	
	/**
	 * キャッシュ生成の抑制
	 */
	function getCacheFilePath($extension = ".html.php"){
		return 
			SOY2HTMLConfig::CacheDir(). SOY2HTMLConfig::getOption("cache_prefix") .
			crc32(SOYCMS_VERSION) .  
			"_cache_" . get_class($this) .'_'. $this->getId() .'_'. $this->getParentPageParam() . md5($this->getClassPath()) . SOY2HTMLConfig::Language() . $extension;
	}
}

//権限チェックフラグ
$soycms_check_permisson = null;

class SOYCMS_WebPageBase extends SOYCMS_HTMLPageBase{
	
	function prepare(){
		global $soycms_check_permisson;
		
		if(is_null($soycms_check_permisson))$soycms_check_permisson = false;
		
		
		//check login
		$session = SOY2Session::get("base.session.UserLoginSession");
		if(!$session || !$session->isLoggedIn()){
			header("Location: " . SOYCMS_ROOT_URL . "admin/login");
			exit;
		}
		
		//check role
		if(!$soycms_check_permisson){
			if(!SOY2Logic::createInstance("base.permission.SOYCMS_CheckPermissionLogic",array(
				"class" => get_class($this)
			))->execute()){
				$this->goError("privilege");
			}
			$soycms_check_permisson = true;
		}
		
		parent::prepare();
		
		$this->addModel("updated",array("visible"=>isset($_GET["updated"])));
		$this->addModel("failed",array("visible"=>isset($_GET["failed"])));
	}
	
	/**
	 * エラー画面を表示する共通
	 */
	function goError($type = "privilege"){
		
		SOY2HTMLConfig::PageDir(SOYCMS_COMMON_DIR . "pages/error/");
		SOY2HTMLConfig::TemplateDir(SOYCMS_COMMON_DIR . "template/error/");
		
		$webPage = SOY2HTMLFactory::createInstance("page_error_" . $type);
		$webPage->display();
		
		exit;
	}
	
	function getLayout(){
		if(isset($_GET["layer"])){
			return "layer.php";
		}
		return "default.php";
	}
}

/**
 * CSRF対策でトークン付きのフォームを生成し、doPost前にtokenチェックを行う
 */
class SOYCMS_UpdatePageBase extends SOYCMS_WebPageBase{
	
	public function prepare(){

		//CSRF対策
		if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
			if(!IS_DEBUG() && !soy2_check_token()){
				SOY2FancyURIController::reload();
			}
		}
		
		parent::prepare();
	}
}

?>