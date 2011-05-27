<?php
SOY2HTMLFactory::importWebPage("page_init_index");
class page_index extends page_init_index{
	
	function init(){
		$this->checkDocumentRoot();
	}
	
	function page_index(){
		
		WebPage::WebPage();
		
		$this->createAdd("init_form","HTMLForm");
		
		//チェック
		$this->addModel("php_version_error",array(
			"visible" => !$this->checkServer()
		));
		
		$this->buildModules();
		
		$this->addLabel("php_version",array(
			"text" => phpversion()
		));
		
		//DocumentRoot以上は環境によってはアクセス出来なくなるので対策
		$dir = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);//dirname($_SERVER["DOCUMENT_ROOT"]);
		if(is_writable($dir)){
			$dir .= "soycms_config";
		}
		
		$this->createAdd("config_dir","HTMLInput",array(
			"name" => "config_dir",
			"value" => $dir
		));
		
		$this->createAdd("user_id","HTMLInput",array(
			"name" => "User[userId]",
			"value" => "root"
		));
		
		$this->createAdd("mail_address","HTMLInput",array(
			"name" => "User[mailAddress]",
			"value" => ""
		));
		
		$this->createAdd("password","HTMLInput",array(
			"name" => "User[password]",
			"value" => ""
		));
		
		$this->createAdd("password_confirm","HTMLInput",array(
			"name" => "password_confirm",
			"value" => ""
		));
		
		
		//書き込み権限のチェック
		$this->addModel("permission_error",array(
			"visible" => !$this->checkPermission()
		));
		$this->addLabel("root_path",array("text" => SOYCMS_ROOT_DIR));
	}
	
	/**
	 * @return boolean
	 */
	function checkServer(){
		//5.1系はアウト
		if(version_compare(phpversion(), "5.2.0") < 0){
			return false;
		}
		
		//PDO
		if(!class_exists("PDO")){
			return false;
		}
		
		//PDO_SQLite	
		if(!in_array("sqlite",PDO::getAvailableDrivers())){
			return false;
		}
		
		//ReflectionClass
		if(!class_exists("ReflectionClass")){
			return false;
		}
		
		if(!function_exists("mb_convert_encoding")){
			return false;
		}
		if(!function_exists("json_encode")){
			return false;
		}
		if(!function_exists("simplexml_load_string")){
			return false;
		}
		
		return true;
	}
	
	function buildModules(){
		
		$this->addModel("pdo_error",array(
			"visible" => !class_exists("PDO")
		));
				
		//PDO_SQLite	
		$this->addModel("sqlite_error",array(
			"visible" => !in_array("sqlite",PDO::getAvailableDrivers())
		));
		$this->addModel("mysql_error",array(
			"visible" => !in_array("mysql",PDO::getAvailableDrivers())
		));
		
		//ReflectionClass
		$this->addModel("refelection_error",array(
			"visible" => !class_exists("ReflectionClass")
		));
		
		$this->addModel("mb_error",array(
			"visible" => !function_exists("mb_convert_encoding")
		));
		
		$this->addModel("json_error",array(
			"visible" => !function_exists("json_encode")
		));
		
		$this->addModel("simplexml_error",array(
			"visible" => !function_exists("simplexml_load_string")
		));
		
	}
	
	function checkPermission(){
		
		//confディレクトリ
		if(!is_writable(SOYCMS_ROOT_DIR . "soy/conf/user/")){
			return false;
		}
		
		if(!is_writable(SOYCMS_ROOT_DIR . "content/")){
			return false;
		}
		
		if(!is_writable(SOYCMS_ROOT_DIR . "tmp/")){
			return false;
		}
		
		return true;
	}
	
	/**
	 * DocumentRootを変更する
	 */
	function checkDocumentRoot(){
		$path = soycms_union_uri($_SERVER["REQUEST_URI"],"index.php");
		$documentRoot = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
		$scriptPath = soy2_realpath(SOYCMS_SCRIPT_FILENAME);
		
		//全然違う場合
		if(strpos($documentRoot,$scriptPath) === false){
			$documentRoot = soy2_realpath(str_replace($path,"/",$scriptPath));
		}
		
		while(true){
			$_path = soycms_union_uri($documentRoot,$path);
			
			if(strcmp($_path,$scriptPath) === 0){
				break;
			}
			
			$tmp = explode("/",$documentRoot);
			array_pop($tmp);
			$documentRoot = implode("/",$tmp);
		}
		
		$_SERVER["OLD_DOCUMENT_ROOT"] = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
		
		if($documentRoot != $_SERVER["OLD_DOCUMENT_ROOT"]){
			$_SERVER["DOCUMENT_ROOT"] = $documentRoot;
			$this->outputDocumentRootConfig($_SERVER["DOCUMENT_ROOT"],$_SERVER["OLD_DOCUMENT_ROOT"]);
			SOY2PageController::jump("");
		}
	}
	
	function getTemplateFilePath(){
		return SOY2HTMLConfig::TemplateDir() . "init/page_init_index.html";
	}
	
	function outputDocumentRootConfig($new,$old){
		
		$tmp = array();
		$tmp[] = "<?php /* generated at " . date("Y-m-d H:i:s") . " */";
		$tmp[] = '$_SERVER["DOCUMENT_ROOT"] = "'.$new.'";';
		$tmp[] = '$_SERVER["OLD_DOCUMENT_ROOT"] = "'.$old.'";';
		
		file_put_contents(SOYCMS_ROOT_DIR ."soy/conf/user/doc.conf.php",implode("\n",$tmp));
	}
	
	function getLayout(){
		return "login.php";
	}
}
?>