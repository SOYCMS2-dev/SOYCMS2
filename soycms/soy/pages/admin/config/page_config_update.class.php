<?php
SOY2::import("zip.SOYCMS_ZipHelper");
/**
 * アップデートの処理を行います。
 */
class page_config_update extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		//アップロード
		if(isset($_FILES["soycms_archive"])){
			$dirname = "soycms-" . md5(time());
			
			try{
				$helper = SOYCMS_ZipHelper::prepare($_FILES["soycms_archive"]["tmp_name"]);
				$helper->uncompress(SOYCMS_ROOT_DIR . "tmp/" . $dirname);
				
				if(!file_exists(SOYCMS_ROOT_DIR . "tmp/" . $dirname . "/soycms/")){
					throw new Exception("failed");
				}
				
				$this->jump("/config/update?target=" . $dirname);
			}catch(Exception $e){
				$_GET["failed"] = 1;
			}
		}
		
		//アップデートの実行
		if(isset($_POST["target"])){
			set_time_limit(0);
			$this->doUpdate($this->targetDir);
			exit;
		}
		
	}
	
	private $targetDir = null;
	
	function init(){
		if(isset($_GET["target"]) && file_exists(SOYCMS_ROOT_DIR . "tmp/" . $_GET["target"])){
			$this->targetDir = SOYCMS_ROOT_DIR . "tmp/" . $_GET["target"] . "/";
		}
		
		$zip = SOYCMS_CommonConfig::get("zip_support",-1);
		if($zip == -1){
			SOYCMS_CommonConfig::put("unzip_support",exec("unzip -v"));
			SOYCMS_CommonConfig::put("zip_support",exec("zip -v"));
			
			$this->reload();
		}
		
		if(!$zip && !class_exists("ZipArchive")){
			echo "[ERROR]zip is not avaiable";
			exit;
		}
		if((int)ini_get("upload_max_filesize") < 3){
			echo "[ERROR]ini_get('upload_max_filesize') is too small(".ini_get("upload_max_filesize") .")";
			exit;
		}
		
		//ログの表示
		if(isset($_GET["log"])){
			$target = SOYCMS_ROOT_DIR . "tmp/" . $_GET["log"] . "/";
			
			if(!file_exists($target . "soycms")){
				echo "target=" . $target;
				echo "\n";
				echo "target is not valid value";
				exit;
			}
			
			
			
			$fp = fopen($target . "log.txt","r");
			echo fread($fp,filesize($target . "log.txt"));
			fclose($fp);
			
			exit;
		}
	}

	function page_config_update() {
		WebPage::WebPage();
		
		$this->addUploadForm("form",array(
			"visible" => (!$this->targetDir)
		));
		
		$this->addForm("update_form",array(
			"visible" => ($this->targetDir)
		));
		
		$this->addLabel("version",array("text" => SOYCMS_VERSION));
		$this->addLabel("new_version",array(
			"text" => (!$this->targetDir) ? "" : file_get_contents($this->targetDir . "soycms/version")  
		));
		
		$this->addInput("update_target",array(
			"name" => "target",
			"value" => basename($this->targetDir)
		));
	}
	
	function getLayout(){
		return "layer.php";
	}
	
	private $log = null;
	private $fp;
	
	/**
	 * アップデートの実行
	 */
	function doUpdate($dir){
		$this->log = $dir . "log.txt";
		file_put_contents($this->log , "");	//clear file
		
		//全ディレクトリの書き込み権限をチェックする
		$this->output("[INFO]it will check all file's permission.'");
		$res = $this->checkIsWritable(SOYCMS_ROOT_DIR);
		if(!$res){
			$this->output("[ERROR]Permission Error");
			exit;
		}
		$this->output("[INFO]Permission is OK!\n");
		
		//元ファイルを検索
		$this->output("[INFO]it will copy files.");
		
		if(SOYCMS_IS_DEBUG()){
			soy2_copy($dir . "soycms/", SOYCMS_ROOT_DIR . "tmp/hoge/");
		}else{
			soy2_copy($dir . "soycms/", SOYCMS_ROOT_DIR);
		}
		$this->output("[INFO]copy is finished!");
		
		$this->output("\n\n\tCONGRATULATION!\n\t\tUPDATE IS FINISHED!");

		
		if($this->fp){
			fclose($this->fp);
		}
	}
	
	function output($buff){
		if(!$this->fp){
			$this->fp = fopen($this->log, "a");
		}
		fwrite($this->fp,$buff . "\n");
	}
	
	function checkIsWritable($dir){
		$dir = soy2_realpath($dir);
		
		if(!file_exists($dir)){
			$this->output("[ERROR]Permission Error:" . $dir);
			return false;
		}
		
		if(!is_writable($dir)){
			$this->output("[ERROR]Permission Error:" . $dir);
			return false;
		}
		
		if(is_dir($dir)){
			$files = soy2_scandir($dir);
			foreach($files as $file){
				$res = $this->checkIsWritable($dir . $file);
				if(!$res){
					$this->output("[ERROR]Permission Error:" . $dir . $file);
					return false;
				}
			}
		}
		
		return true;
		
	}
}
?>