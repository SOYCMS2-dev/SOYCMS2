<?php
$theme = "gray";
$session = SOY2Session::get("site.session.SiteLoginSession");
$userSession = SOY2Session::get("base.session.UserLoginSession");
if($session && $session->getId()){
	$theme = $session->getTheme();
}else{
	$theme = $userSession->getTheme();
	
}
?>
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme; ?>/css/styles.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme; ?>/css/design.css" type="text/css" media="all" />
<link rel="shortcut icon" href="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme; ?>/favicon.ico" />
<link rel="icon" href="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme; ?>/favicon.ico" />
<!--[if IE 6]>
<script src="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme;  ?>/js/minmax.js" type="text/javascript"></script>
<script src="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme;  ?>/js/DD_belatedPNG_0.0.8a.js" type="text/javascript"></script>
<![endif]-->
<script src="<?php echo SOYCMS_COMMON_URL; ?>js/jquery.js" type="text/javascript"></script>
<script src="<?php echo SOYCMS_COMMON_URL; ?>js/jquery-ui.js" type="text/javascript"></script>
<script src="<?php echo SOYCMS_COMMON_URL; ?>js/jquery.cookie.js" type="text/javascript"></script>
<script src="<?php echo SOYCMS_COMMON_URL . "cp_theme/" . $theme; ?>/js/design.js" type="text/javascript"></script>
<script src="<?php echo SOYCMS_COMMON_URL; ?>js/main.js?v=<?php SOYCMS_VERSION; ?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo SOYCMS_COMMON_URL; ?>js/soy2_date_picker/soy2_date_picker.js"></script>
<script type="text/javascript">
var SOYCMS_ROOT_URL = "<?php echo SOYCMS_ROOT_URL; ?>";
var SOYCMS_SITE_URL = "<?php echo SOYCMS_SITE_URL; ?>";
</script>
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL; ?>js/jquery-ui/jquery-ui.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL; ?>css/ext.css" type="text/css" media="all" />
<?php /* この二個は任意のタイミングで読み込むか検討 */ ?>
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL; ?>wysiwyg/default/panel.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?php echo SOYCMS_COMMON_URL; ?>css/editor.css" type="text/css" media="all" />
<?php
if(defined("SOYCMS_LOGIN_SITE_ID")){
	$css = "content/" . SOYCMS_LOGIN_SITE_ID . ".css";
	if(file_exists(SOYCMS_ROOT_DIR . $css)){
		echo '<link rel="stylesheet" href="'.SOYCMS_ROOT_URL . $css .'" type="text/css" media="all" />';
	}
}
?>