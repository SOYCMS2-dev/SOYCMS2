<?php
$session = SOY2Session::get("site.session.SiteLoginSession");
$user = SOY2Session::get("base.session.UserLoginSession");
$config = $session->getConfig();
?>
<div id="cms-version">
	<p>SOY CMS <?php echo SOYCMS_VERSION; ?></p>
</div>

<div id="user-info">
	<ul>
		<li class="logout"><a href="<?php echo SOYCMS_ROOT_URL; ?>admin/logout"><em>ログアウト</em></a></li>
		<?php if($session){ ?>
		<li class="check"><a href="<?php echo $session->getSiteRootURL(); ?>"><em>WEBサイトを確認</em></a></li>
		<?php } ?>
	</ul>
	<?php if($session){ ?>
	<p class="user"><a href="<?php echo SOYCMS_ROOT_URL; ?>admin/user/profile"><?php echo $user->getName(); ?></a></p>
	<?php } ?>
</div>

<div id="site-id">
	<h1>
		<a href="<?php echo SOYCMS_ROOT_URL; ?>site/">
			<?php if(@$config["header_icon"] == 0){ ?>
				<img src="<?php echo SOYCMS_COMMON_URL; ?>cp_theme/gray/img/logo.png" alt="SOY CMS2" width="150" height="64" class="png" />
			<?php }else{ ?>
				<img src="<?php echo SOYCMS_ROOT_URL . "content/header_icon_" . SOYCMS_LOGIN_SITE_ID; ?>.png" alt="SOY CMS" width="105" height="64" class="png" />
			<?php } ?>
		</a>
	</h1>
	<?php if($session){ ?>
	<dl>
		<dt>WEBサイト名</dt>
		<dd><a href="<?php echo $session->getSiteRootURL(); ?>"><?php echo $session->getSiteName(); ?></a></dd>
	</dl>
	<?php } ?>
</div>