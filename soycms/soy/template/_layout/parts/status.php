<?php
$visible = (isset($_GET["updated"]) || isset($_GET["created"]) || isset($_GET["failed"]) || isset($_GET["deleted"]) || isset($_GET["clear"]));
if($visible){
$style = null;	
$custom = SOYCMS_DataSets::get("config_custom",array());
if(@$custom["popup_icon"]==1)$style="style=\"background:url('".SOYCMS_ROOT_URL . "content/popup_icon_" . SOYCMS_LOGIN_SITE_ID . ".png') no-repeat scroll right top transparent;\"";
?>
<div id="user-status" <?php echo $style; ?>>
	<div class="inner">
		<dl>
			<dt><span>ユーザーステータス</span></dt>
			<dd>
				<?php if(isset($_GET["updated"])){ ?>更新しました<?php }?>
				<?php if(isset($_GET["created"])){ ?>作成しました<?php }?>
				<?php if(isset($_GET["deleted"])){ ?>削除しました<?php }?>
				<?php if(isset($_GET["clear"])){ ?>キャッシュを削除しました<?php }?>
				<?php if(isset($_GET["failed"])){ ?>エラーが発生しました<?php }?>
			</dd>
		</dl>
	</div>
</div>
<!--  // #user-status -->
<?php
}
?>