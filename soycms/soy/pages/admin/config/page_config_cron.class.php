<?php
/**
 * @title クロンの設定
 */
class page_config_cron extends SOYCMS_WebPageBase{
	
	function doPost(){
		
		if(isset($_POST["execute_now"]) && soy2_check_token()){
			$this->executeNow();
		}
		
		$this->jump("/config#tab4?updated");
		
		
	}

	function page_config_cron(){
		WebPage::WebPage();
		
		$this->addForm("form",array(
			"action" => soycms_create_link("/config/cron")
		));
		
		
		$filepath = SOYCMSConfigUtil::get("config_dir") . "cron.log";
		$time = (file_exists($filepath)) ? filemtime($filepath) : null;
		
		$this->addLabel("last_run_time",array(
			"text" => ($time) ? date("Y-m-d H:i:s", $time) : "-"
		));
		
		$this->addTextArea("cron_sample",array(
			"value" => $this->getCronSampleCode()
		));
		
		$this->addTextArea("cron_log",array(
			"value" => ($time) ? file_get_contents($filepath) : "-"
		));
		
		$this->addModel("cron_loaded",array(
			"visible" => $time
		));
		
		//1日1回系
		$filepath = SOYCMSConfigUtil::get("config_dir") . "cron_daily.log";
		$time = (file_exists($filepath)) ? filemtime($filepath) : null;
		
		$this->addLabel("daily_last_run_time",array(
			"text" => ($time) ? date("Y-m-d H:i:s", $time) : "-"
		));
		
		$this->addTextArea("cron_daily_log",array(
			"value" => ($time) ? file_get_contents($filepath) : "-"
		));
		
		$this->addModel("cron_daily_loaded",array(
			"visible" => $time
		));
	}
	
	function executeNow(){
		$command = $this->getCommand();
		$command .= " > /dev/null &";
		
		exec($command);
	}
	
	function getCommand($command = "proc.php"){
		$path = SOYCMS_COMMON_DIR . "job/{$command}";
		return 'php "'.$path.'" --config-dir="'.SOYCMSConfigUtil::get("config_dir").'" --db-dir="'.SOYCMSConfigUtil::get("db_dir").'"';
	}
	
	function getCronSampleCode(){
		
		$data = array();
		$data[] = '#１時間に一回実行(自動更新機能など利用頻度が高いプラグイン用)';
		$data[] = '0 * * * * ' . $this->getCommand();
		$data[] = '#１日一回(AM01:00)実行(レポートやバックアップなど)';
		$data[] = '0 1 * * * ' . $this->getCommand("proc_daily.php");
		return implode("\n",$data);
	}
	
	function getLayout(){
		return "blank.php";
	}
}