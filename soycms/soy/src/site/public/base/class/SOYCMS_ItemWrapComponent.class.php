<?php
/**
 * ダイナミック編集で要素毎に呼ばれたりします。
 */
class SOYCMS_ItemWrapComponent {
	
	public static function startTag($type,$id = null,$configLink = null,$editLink = null){
		
		if(!defined("SOYCMS_EDIT_DYNAMIC") || SOYCMS_EDIT_DYNAMIC == false){
			return;
		}
		
		if(!$id)$id = $type;
		
		$class = "soycms_item_wrap_" . $type;
		$title = $id;
		
		switch($type){
			case "library":
			case "navigation":
				$configLink = SOYCMS_ADMIN_ROOT_URL . "site/page/"  .$type . "/detail?id=" . $id;
				break;
			case "label":
				$configLink = SOYCMS_ADMIN_ROOT_URL . "site/page/label/detail/"  .$configLink;
				break;
			case "block":
				break;
			case "entry":
				if(!$editLink && !$configLink)return;
				break;
			default:
				break;
		}
		
		//Roleで表示を変えます。
		$session = SOY2Session::get("site.session.SiteLoginSession");
		if(!$session->hasRole("designer")){
			$configLink = "";
		}
		
		echo "<div class='soycms_item_wrap ${class}'>";
			echo "<div class='soycms_wrap_title'>";
				echo "<ul>";
				if($editLink){
					$_title = ($id == "entry") ? "編集" : "投稿";
					echo "<li class=\"soycms_item_wrap_menu_post\">" .
							"<a href=\"${editLink}\" title=\"$_title\">$title</a>" .
						"</li>";
				}
				if($configLink){
					echo "<li class=\"soycms_item_wrap_menu_edit\">" .
							"<a href=\"${configLink}\" title=\"編集\"><span>編集</span></a>" .
						"</li>";
				}
				echo "</ul>";
				echo "<p>${title}</p>";
			echo "</div>";
	}
	
	function entryEditTag($id,$title){
		
		if(!defined("SOYCMS_EDIT_DYNAMIC") || SOYCMS_EDIT_DYNAMIC == false){
			return;
		}
		
		$link = SOYCMS_ADMIN_ROOT_URL . "entry/detail/" . $id;
		
		if($link)echo "<a href=\"${link}\">Edit</a>";	
	}
	
	public static function endTag(){
		if(!defined("SOYCMS_EDIT_DYNAMIC") || SOYCMS_EDIT_DYNAMIC == false){
			return;
		}
		
		echo "</div><!-- //.soycms_item_wrap -->";
	}
}
?>