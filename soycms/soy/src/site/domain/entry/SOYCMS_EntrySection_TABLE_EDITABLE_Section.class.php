<?php

class SOYCMS_EntrySection_TABLE_EDITABLE_Section extends SOYCMS_EntrySection{
	
	function build(){
		$values = $this->getValue();
		parse_str($values,$values);
		
		$attribute = array();
		$attribute["cellpadding"] = $values["TABLE_CELPADDING"];
		$attribute["cellspacing"] = $values["TABLE_CELSPACINFG"];
		if(strlen($values["TABLE_BORDER"])>0)$attribute["border"] = $values["TABLE_BORDER"];
		if(strlen($values["TABLE_SUMMARY"])>0)$attribute["summary"] = $values["TABLE_SUMMARY"];
		if(@$values["TABLE_FULL"]){
			$attribute["style"] = "width:100%;";	
		}else if(strlen($values["TABLE_WIDTH"])>0){
			$attribute["style"] = "width:".$values["TABLE_WIDTH"].$values["TABLE_WIDTH_TYPE"].";";
		}
		$_attribute = array();
		foreach($attribute as $key => $value){
			$_attribute[] = $key.'="'.htmlspecialchars($value,ENT_QUOTES).'"'; 
		}
		$attribute = " " . implode(" ",$_attribute);
		
		
		$thead = "";
		
		$th_attr = "";
		if(strlen($values["TABLE_HEADER_POSITON"])){
			$th_attr = " style=\"text-align:".htmlspecialchars($values["TABLE_HEADER_POSITON"],ENT_QUOTES)."\"";
		}
		
		if($values["TABLE_HEADER"] == "upper" || $values["TABLE_HEADER"] == "both"){
			
			$thead = array();
			$thead[] = "<thead>";
			$thead[] = "<tr>" .
				(($values["TABLE_HEADER"] == "both") ? '<th>&nbsp;</th>' : "") . 
				str_repeat("<th$th_attr>&nbsp;</th>",$values["TABLE_COL_COUNT"]) . 
				"</tr>";
			$thead[] = "</thead>";
			$thead = implode("\n",$thead);
		}
		
		$tbody = array();
		if(strlen($values["TABLE_CAPTION"])>0){
			$tbody[] = '<caption>'.$values["TABLE_CAPTION"].'</caption>';
		}
		for($i=0;$i<$values["TABLE_ROW_COUNT"];$i++){
			$tbody[] = "<tr>";
			
			if($values["TABLE_HEADER"] == "left" || $values["TABLE_HEADER"] == "both"){
				$key = $i+1;
				$tbody[] = "<th$th_attr>$key</th>";
			}
			
			for($j=0;$j<$values["TABLE_COL_COUNT"];$j++){
				$tbody[] = "<td>&nbsp;</td>";
			}
			$tbody[] = "</tr>";
		}
		$tbody = implode("\n",$tbody);
		
		//置換
		$content = $this->getContent();
		
		$content = str_replace("#TABLE_GEN_ATTRIBUTE#",$attribute,$content);
		$content = str_replace("#TABLE_GEN_THEAD#",$thead,$content);
		$content = str_replace("#TABLE_GEN_TBODY#",$tbody,$content);
		$content = str_replace("#TABLE_GEN_TFOOT#","",$content);
		
		$this->setContent($content);
		
		$this->setType("wysiwyg");
	}

}
?>