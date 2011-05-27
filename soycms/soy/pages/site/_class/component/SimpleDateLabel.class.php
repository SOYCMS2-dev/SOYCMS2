<?php

class SimpleDateLabel extends HTMLLabel{
	
	private $date;

	function execute(){
		
		$this->setText($this->parseDate());
		
		parent::execute();
	}
	
	function parseDate(){
		
		$myDate = $this->getDate();
		
		$now = getdate();
		$date = getdate($myDate);
		
		if($now["year"] != $date["year"]){
			return date("Y-m-d H:i:s",$myDate); 
		}
		
		if($now["mon"] != $date["mon"]){
			return date("m-d H:i:s",$myDate);
		}
		
		if($now["mday"] != $date["mday"]){
			return date("m-d H:i:s",$myDate);
		}
		
		return date("H:i:s",$myDate);
	}

	function getDate() {
		if(!is_numeric($this->date))return null;
		return $this->date;
	}
	function setDate($date) {
		$this->date = $date;
	}
}
?>