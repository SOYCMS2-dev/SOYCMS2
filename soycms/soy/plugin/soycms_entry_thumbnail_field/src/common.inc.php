<?php

class SOYCMS_EntryThumbnailFieldHelper{
	
	public static function getValue($entryId){
		
		$enable = SOYCMS_EntryAttribute::get($entryId,"soycms_entry_thumbnail_field",null);
		$thumbnail = SOYCMS_EntryAttribute::get($entryId,"soycms_entry_thumbnail_field.thumbnail",null);
		$text = SOYCMS_EntryAttribute::get($entryId,"soycms_entry_thumbnail_field.thumbnail_text",null);
		
		if(strlen($thumbnail)<1){
			$thumbnail = soycms_get_file_url("themes/default/img/img1.gif");
		}
		
		return array(
			"enabled" => $enable,
			"image" => $thumbnail,
			"text" => $text
		);	
	}
	
	public static function putValue($entryId,$array){
		SOYCMS_EntryAttribute::put($entryId,"soycms_entry_thumbnail_field",$array["enabled"]);
		
		if(!$array["enabled"]){
			//$array["image"] = null;
			//$array["text"] = null;
		}
		
		$thumbnail = SOYCMS_EntryAttribute::put($entryId,"soycms_entry_thumbnail_field.thumbnail",$array["image"]);
		$text = SOYCMS_EntryAttribute::put($entryId,"soycms_entry_thumbnail_field.thumbnail_text",$array["text"]);	
	}
	
}

?>
