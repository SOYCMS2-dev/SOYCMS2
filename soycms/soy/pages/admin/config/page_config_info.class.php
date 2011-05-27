<?php
/**
 * @title SOYCMS２の情報
 */
class page_config_info extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["check_exec"])){
			SOYCMS_CommonConfig::put("exec_support",exec("php -v"));
			echo $this->getExecSupport();
			echo ' <span class="s">' . date("Y-m-d H:i:s") . "</span>";
			exit;
		}
		
		if(isset($_POST["check_zip"])){
			
			SOYCMS_CommonConfig::put("unzip_support",exec("unzip -v"));
			SOYCMS_CommonConfig::put("zip_support",exec("zip -v"));
			echo $this->getZipSupport();
			echo ' <span class="s">' . date("Y-m-d H:i:s") . "</span>";
			exit;
		}
		
		
	}
	
	
	function page_config_info(){
		WebPage::WebPage();
	}
	
	function execute(){
		$this->buildPage();
	}
	
	function buildPage(){
		$this->addLabel("root_dir",array("text" => SOYCMS_ROOT_DIR));
		$this->addLabel("soycms_version",array("text" => SOYCMS_VERSION));
		$this->addLabel("php_version",array("text" => phpversion()));
		$this->addLabel("plugins",array("html"=>$this->getPlugins()));
		$this->addLabel("zip",array("text" => $this->getZipSupport()));
		$this->addLabel("exec",array("text" => $this->getExecSupport()));
		
		$this->addLabel("phpinfo_text",array("text" => $this->getReports()));
		
		$this->addModel("zip_enabled",array(
			"visble" => SOYCMS_CommonConfig::get("zip_support",0)
		));
	}
	
	function getPlugins(){
		$dir = SOYCMS_COMMON_DIR . "plugin/";
		$files = soy2_scandir($dir);
		return "<span>" . implode("</span>, <span>",$files) . "</span>";
	}
	
	/**
	 * zip対応状況の表示
	 */
	function getZipSupport(){
		if(class_exists("ZipArchive")){
			return "php_zip";
		}
		
		$zip = SOYCMS_CommonConfig::get("zip_support",-1);
		$unzip = SOYCMS_CommonConfig::get("unzip_support",-1);
		if($zip === -1){
			return "[unknown]";
		}
		
		$res = array();
		$res[] =  ($zip) ? "zip_enable" : "zip_disable";
		$res[] =  ($unzip) ? "unzip_enable" : "unzip_disable";
		
		return implode(",",$res);
	}
	
	function getExecSupport(){
		$config = SOYCMS_CommonConfig::get("exec_support",-1);
		if($config === -1){
			return "[unknown]";
		}
		
		return ($config) ? "enable" : "disable";
	}
	
	function getLayout(){
		return "blank.php";
	}
	
	function getReports(){
		$str = array();

		$str[] = 'PHP Version:			'.phpversion();
		$str[] = '';
		$str[] = 'PHP SAPI NAME:			'.php_sapi_name();
		$str[] = 'PHP SAFE MODE:			'.(ini_get("safe_mode")? "Yes" : "No");
		$str[] = 'MAGIC_QUOTE_GPC:		'.( get_magic_quotes_gpc() ? "Yes" : "No" );
		$str[] = 'SHORT_OPEN_TAG:		'.( ini_get("short_open_tag") ? "Yes" : "No" );
		$str[] = '';
		$str[] = 'MEMORY_LIMIT:			'.ini_get("memory_limit")." Bytes";
		if(function_exists("memory_get_usage")){
		$str[] = 'Memory Usage:			'.number_format(memory_get_usage())." Bytes";
		$str[] = '						'.number_format(memory_get_usage(true))." Bytes (Real)";
		}
		if(function_exists("memory_get_peak_usage")){
		$str[] = '						'.number_format(memory_get_peak_usage())." Bytes (Peak)";
		$str[] = '						'.number_format(memory_get_peak_usage(true))." Bytes (Peak, Real)";
		}
		$str[] = '';
		$str[] = 'MAX_EXECUTION_TIME:	'.ini_get("max_execution_time") ." sec.";
		$str[] = 'POST_MAX_SIZE:			'.ini_get("post_max_size")." Bytes";
		$str[] = 'UPLOAD_MAX_FILESIZE:	'.ini_get("upload_max_filesize")." Bytes";
		$str[] = '';
		$str[] = 'mb_string:			'.( extension_loaded("mbstring") ? "Yes" : "No" );
		$str[] = 'PDO:				'.( extension_loaded("PDO") ? "Yes" : "No" );
		$str[] = 'PDO_SQLite:			'.( extension_loaded("PDO_SQLITE") ? "Yes" : "No" );
		$str[] = 'PDO_MySQL:			'.( extension_loaded("PDO_MySQL") ? "Yes" : "No" );
		$str[] = 'Standard PHP Library:	'.( extension_loaded("SPL") ? "Yes" : "No" );
		$str[] = 'SimpleXML:			'.( extension_loaded("SimpleXML") ? "Yes" : "No" );
		$str[] = 'JSON:				'.( extension_loaded("json") ? "Yes" : "No" );
		$str[] = 'Services_JSON:		'.( class_exists("Services_JSON") ? "Yes" : "No" );
		$str[] = 'ZIP:					'.( extension_loaded("zip") ? "Yes" : "No" );
		$str[] = 'ZipArchive:			'.( class_exists("ZipArchive") ? "Yes" : "No" );
		$str[] = 'Archive_Zip:		 	'.( class_exists("Archive_Zip") ? "Yes" : "No" );
		$str[] = 'OpenSSL:			'.( extension_loaded("openssl") ? "Yes" : "No" );
		$str[] = 'HASH:				'.( extension_loaded("hash") ? "Yes" : "No" );
		$str[] = 'GD:					'.( extension_loaded("GD") ? "Yes" : "No" );
		$str[] = '';
		$str[] = 'Module/CGI			'.( (stripos(php_sapi_name(),"cgi")!==false) ? "CGI" : "Module");
		$str[] = 'Rewrite				'.( function_exists("apache_get_modules") ? ( in_array("mod_rewrite", apache_get_modules()) ? "OK" : "NG") : "Unknown");
		$str[] = '';
		$str[] = 'USER_AGENT:		'.@$_SERVER["HTTP_USER_AGENT"];
		$str[] = 'REQUEST_URI:		'.@$_SERVER["REQUEST_URI"];
		$str[] = 'SCRIPT_NAME:		'.@$_SERVER["SCRIPT_NAME"];
		$str[] = 'PATH_INFO:			'.@$_SERVER["PATH_INFO"];
		$str[] = 'QUERY_STRING:		'.@$_SERVER["QUERY_STRING"];
		$str[] = '';
		$str[] = 'DOCUMENT_ROOT:	'.@$_SERVER["DOCUMENT_ROOT"];
		$str[] = 'SCRIPT_FILENAME:	'.@$_SERVER["SCRIPT_FILENAME"];
	
		return implode("\n",$str);
	}
}