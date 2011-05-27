<?php

class HTMLDateInput extends HTMLInput{

	function getStartTag(){
		return $this->getWrapperStart() . $this->getWrapperEnd();
	}

	function setValue($value){
		
		if(!is_numeric($value) && strlen($value)>0){
			$value = time();
		}
		$array = array(
			"date" => (strlen($value)>0) ? date("Y-m-d",$value) : "",
			"time" => (strlen($value)>0) ? date("H:i",$value) : ""
		);
		parent::setValue($array);
	}

	function getWrapperStart(){

		$html = array();
		$html[] = '<input type="text" class="m-area date-input" size="11" name="<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["name"]; ?>[0]" value="<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["date"]; ?>" />';
		$html[] = '@<input type="text" class="m-area time-input" size="9" name="<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["name"]; ?>[1]" value="<?php echo $'.$this->getPageParam().'["'.$this->getId().'"]["time"]; ?>" />';

		return implode("",$html);
	}

	function getWrapperEnd(){
		return '';
	}
}
?>