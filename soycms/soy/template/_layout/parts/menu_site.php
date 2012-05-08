<?php
$session = SOY2Session::get("site.session.SiteLoginSession");
if($session && $session->getId() && $session->hasSiteRole()){
PluginManager::load("soycms.site.menu.*");
$menus = PluginManager::invoke("soycms.site.menu.*")->getMenus();
?>

<div id="cms-menu-section1" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/","メインメニュー"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/","管理画面トップ"); ?>
			<?php if($session->hasRole("super")) : ?><?php soycms_print_menu("site/config","WEBサイトの全体設定"); ?>
			<?php soycms_print_menu("site/config/custom","管理画面のテーマ変更"); ?><?php endif; ?>
			<?php if($session->hasRole("designer") || $session->hasRole("super")) : ?><?php soycms_print_menu("fm/","ファイルマネージャー"); ?><?php endif; ?>
			<?php
			if(isset($menus["main"])){
			foreach($menus["main"] as $array){
			soycms_print_menu($array[1],$array[0]);
			}
			}
			?>
			
			<?php if(SOYCMS_IS_DEBUG()){ ?><!-- <?php soycms_print_menu("site/?init","再生成(dev)"); ?> --><?php } ?>
			
		</ul>
	</div>
</div>
<!--  // #cms-menu-section1 -->
	
<?php if($session->hasRole("editor") || $session->hasRole("author") ) : ?>
<div id="cms-menu-section2" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/entry/","コンテンツの管理"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/entry/create","記事を作成","/site/entry/create"); ?>
			<?php soycms_print_menu("site/entry","記事の管理","/site/entry/?|/site/entry/detail/.*?|/site/entry/list/.*|/site/entry/search.*"); ?>
			<?php soycms_print_menu("site/entry/comment","コメントの管理"); ?>
			<?php soycms_print_menu("site/entry/trackback","トラックバックの管理"); ?>
			<?php
				if(isset($menus["entry"])){
					foreach($menus["entry"] as $array){
						soycms_print_menu($array[1],$array[0]);
					}
				}
			?>
			
		</ul>
	</div>
</div>
<!--  // #cms-menu-section2 -->
<?php endif; ?>

<?php if($session->hasRole("designer")) : ?>
<div id="cms-menu-section3" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/page/","WEBデザインの管理"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/page/list","ディレクトリの管理",'/site/page/list.*|/site/page/detail.*'); ?>
			<?php
				soycms_print_menu(
					"site/page/template","テンプレートの管理","/site/page/template.*|/site/page/library.*|/site/page/navigation.*",
					array(
						array("site/page/library","ライブラリの管理","/site/page/library.*"),
						array("site/page/navigation","ナビゲーションの管理","/site/page/navigation.*"),
					)
				);
			?>
			
			<?php soycms_print_menu("site/page/label","ラベル・タグの管理","/site/page/label.*"); ?>
			<?php soycms_print_menu("site/page/snippet","投稿ボタンの管理","/site/page/snippet.*"); ?>
			
			<?php soycms_print_menu("site/page/field","カスタムフィールドの管理","/site/page/field.*"); ?>
			
			<?php if($session->hasRole("super")) { ?><?php soycms_print_menu("site/user/workflow","公開権限設定"); ?><?php } ?>
			
			<?php
				if(isset($menus["page"])){
					foreach($menus["page"] as $array){
						soycms_print_menu($array[1],$array[0]);
					}
				}
			?>
			
			<?php
				if(file_exists(SOYCMS_SITE_DIRECTORY . ".i18n")){
					soycms_print_menu("site/page/string","文言の管理","/site/page/string.*");
				}
			?>
		</ul>
	</div>
</div>
<!--  // #cms-menu-section3 -->
<?php endif; ?>
	
<?php if($session->hasRole("super")) : ?>
<div id="cms-menu-section4" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/plugin/","プラグインの設定"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/plugin","プラグインの管理"); ?>
			<?php
				if(isset($menus["plugin"])){
					foreach($menus["plugin"] as $array){
						soycms_print_menu($array[1],$array[0]);
					}
				}
			?>
		</ul>
	</div>
</div>
<!--  // #cms-menu-section4 -->
<?php endif; ?>

<?php if($session->hasRole("super")) : ?>
<div id="cms-menu-section5" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/user/","ユーザーの設定"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/user","サイトユーザーの管理"); ?>
			<?php soycms_print_menu("site/user/group","グループの管理"); ?>
			<?php
				if(isset($menus["user"])){
					foreach($menus["user"] as $array){
						soycms_print_menu($array[1],$array[0]);
					}
				}
			?>
		</ul>
	</div>
</div>
<!--  // #cms-menu-section5 -->
<?php endif; ?>

<?php if($session->hasRole("super")) : ?>
<div id="cms-menu-section6" class="section">
	<div class="title">
		<h2><?php soycms_print_menu_head("site/manager/","サイトのデータ管理"); ?></h2>
		<p class="btn" title="パネルを閉じる"><span title="パネルを開く"></span></p>
	</div>
	<div class="content">
		<ul>
			<?php soycms_print_menu("site/manager","サイトのデータ管理"); ?>
			<?php soycms_print_menu("site/manager/contents","コンテンツのエクスポート"); ?>
			<?php soycms_print_menu("site/manager/design","デザインのエクスポート"); ?>
			
			<?php
				if(isset($menus["manager"])){
					foreach($menus["manager"] as $array){
						soycms_print_menu($array[1],$array[0]);
					}
				}
			?>
		</ul>
	</div>
</div>
<!--  // #cms-menu-section6 -->
<?php endif; ?>

<?php } ?>