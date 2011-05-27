<?php
PluginManager::load("soycms.app.connector");
$menus = PluginManager::invoke("soycms.app.connector",array("mode" => "menu"))->getMenus();

//session
$session = SOY2Session::get("site.session.SiteLoginSession");

foreach($menus as $key => $array){
	if(!$session->hasRole("super") && !$session->hasRole($key))continue;
	$appTitle = $array["title"];
	$menu = $array["menu"];
	if(empty($appTitle))continue;
?>
<div id="cms-app-<?php echo $key; ?>-section" class="section">
	<div class="title">
		<h2 style="padding:3px 0;"><?php echo $appTitle; ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p> 
	</div>
	<div class="content">
		<?php
			echo $menu;
		?>
	</div>
</div>
<!--  // #cms-menu-section1 -->
<?php } ?>