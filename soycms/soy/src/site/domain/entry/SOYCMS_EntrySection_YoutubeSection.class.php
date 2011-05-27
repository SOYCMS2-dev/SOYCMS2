<?php

class SOYCMS_EntrySection_YoutubeSection extends SOYCMS_EntrySection{
	
	function build(){
		$values = $this->getValue();
		parse_str($values,$values);
		$url = $values["YOUTUBE_URL"];
		$values["YOUTUBE_OBJECT"] = preg_replace("/\/watch\?v=/","/v/",$url);
		
		//
		$html = file_get_contents($url);		
		preg_match('/<title>([^<]*)<\/title>/i',$html,$tmp);
		$title = @$tmp[1];
		
		$content = $this->getContent();
		
		$content = str_replace("#YOUTUBE_TITLE#",$title,$content);
		$content = str_replace("#YOUTUBE_OBJECT#",$values["YOUTUBE_OBJECT"],$content);
		
		$this->setContent($content);
	}

}
?>