<?php

class SOYCMS_EntryThumbnailField extends SOYCMS_EntryCustomFieldBase{
	
	function SOYCMS_EntryThumbnailField(){
		include_once(dirname(__FILE__) . "/src/common.inc.php");
	}

	/**
	 * @return string
	 */
	function getForm(SOYCMS_Entry $entry){
		
		$value = SOYCMS_EntryThumbnailFieldHelper::getValue($entry->getId());
		
		$disp = ($value["enabled"]) ? "" : "display:none;";
		$checked = ($value["enabled"]) ? "checked" : "";
		
		$html = '<div>';
		
		$html .= '<div class="label">' .
					'<h4>一覧ページに使用する画像</h4>' .
				'</div>';
				
		$html .= '<input type="hidden" name="soycms_entry_thumbnail_field[enabled]" value="0" />';
				
		$html .= '<div class="item">' .
					'<input type="checkbox" name="soycms_entry_thumbnail_field[enabled]" onclick="$(\'#soycms_entry_thumbnail_wrap\').toggle($(this).prop(\'checked\'));" value="1" '.$checked.'/>' .
					'<label for="">サムネイルを設定する</label>' .
					'</div>';
		
				
		$html .= '<div id="soycms_entry_thumbnail_wrap" class="item" style="'.$disp.'">' .
				'<div class="content">' .
				'<table class="form-table">' .
				'<tr>' .
					'<th>画像URL</th>' .
					'<td><input type="text" id="soycms_thumbnail_input" class="s-area intro" size="40" name="soycms_entry_thumbnail_field[image]" value="'.htmlspecialchars($value["image"],ENT_QUOTES).'" /> ' .
					'<span><input type="button" class="s-btn" value="参照" onclick="aobata_editor.show_attachments(function(img,link){$(\'#soycms_thumbnail_input\').val(img);$(\'#soycms_thumbnail_img\').attr(\'src\',img)});" /></span>' .
					((strlen($value["image"])>0) ? '<br /><img id="soycms_thumbnail_img" src="'.htmlspecialchars($value["image"],ENT_QUOTES).'" />' : "") .
					'</td>' .
				'</tr>' .
				'<tr>' .
					'<th>テキスト</th>' .
					'<td>' .
					'<textarea type="text" class="s-area liq-area" rows="2" name="soycms_entry_thumbnail_field[text]">'.htmlspecialchars($value["text"],ENT_QUOTES).'</textarea>' .
					'</td>' .
				'</tr>' .
				'</table>' .
				
				"</div><!-- // .content -->" .
				"</div><!-- // .item -->";
			
		$html .= "</div>";
		
		
		return $html;
	}

	/**
	 * doPost
	 */
	function doPost(SOYCMS_Entry $entry){
		if(isset($_POST["soycms_entry_thumbnail_field"])){
			if(isset($_POST["soycms_entry_thumbnail_field"]["image"]) &&
			   isset($_POST["soycms_entry_thumbnail_field"]["text"])
			){
				SOYCMS_EntryThumbnailFieldHelper::putValue($entry->getId(),$_POST["soycms_entry_thumbnail_field"]);
			}
		}
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj,SOYCMS_Entry $entry,$mode = "list"){
		//詳細時はスキップ
		if($mode == "detail"){
			return;
		}
		
		$value = SOYCMS_EntryThumbnailFieldHelper::getValue($entry->getId());
		
		$htmlObj->addLabel("thumbnail_text",array(
			"html" => $value["text"],
			"soy2prefix" => "cms"
		));
		
		$htmlObj->addImage("thumbnail_image",array(
			"src" => $value["image"],
			"soy2prefix" => "cms"
		));
		
	}

	/**
	 * @onDelete
	 */
	function onDelete($id){


	}

}
PluginManager::extension("soycms.site.entry.field","soycms_entry_thumbnail_field","SOYCMS_EntryThumbnailField");