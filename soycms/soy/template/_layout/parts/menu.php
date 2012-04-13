<!--

<div id="cms-menu-help">
	<p>まずここから<br />操作する○○を<br />選択します</p>
</div>
-->

<?php soy2html_layout_include("parts/menu_app.php"); ?>

<!-- サイトにログインしている場合に下を表示 -->
<?php soy2html_layout_include("parts/menu_site.php"); ?>

<!-- このメニューは全てのユーザに表示する必要は無い -->
<?php
$session = SOY2Session::get("base.session.UserLoginSession");
?>
<div id="cms-menu-section_total" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("admin/","CMS管理"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p> 
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("admin/site","サイト管理"); ?>
			<?php if($session->getLevel() < 1){ ?>
			<?php soycms_print_menu("admin/site/create","サイト作成"); ?>
			<?php soycms_print_menu("admin/user","ユーザー一覧"); ?>
			<?php soycms_print_menu("admin/config","設定"); ?>
				<?php if(SOYCMS_IS_DEBUG()){ ?>
				<!-- <?php soycms_print_menu("admin/init","初期化"); ?> -->
				<?php } ?>
			<?php } ?>
			
		</ul>
	</div>
</div>