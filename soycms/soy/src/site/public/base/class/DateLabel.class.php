<?php
class DateLabel extends HTMLLabel{
	
	private $defaultFormat = null;
	
	function execute(){
			
		$format = $this->getAttribute("cms:format");
		
		if(!$format){
			if(is_null($this->defaultFormat) || strlen($this->defaultFormat) == 0){
				$format = "Y-m-d(W) H:i:s";
			}else{
				$format = $this->defaultFormat;
			}
		}
		
		$pubdate = $this->getAttribute("cms:pubdate");
		
		if($pubdate){
			$this->setAttribute("pubdate",soy2_date($pubdate,$this->text));
		}
		$datetime = $this->getAttribute("cms:datetime");
		if($datetime){
			$this->setAttribute("datetime",soy2_date($datetime,$this->text));
		}
		
		
		$this->setText($this->text);
		
		$this->_soy2_innerHTML ='<?php echo soy2_date("'.addslashes($format).'",$'.$this->_soy2_pageParam.'["'.$this->_soy2_id.'"]); ?>';
	}

	function getDefaultFormat() {
		return $this->defaultFormat;
	}
	function setDefaultFormat($defaultFormat) {
		$this->defaultFormat = $defaultFormat;
	}
	
	function setValue($value){
		$this->setText($value);
	}
}
?>