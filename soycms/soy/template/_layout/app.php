<!DOCTYPE html>
<html lang="ja" dir="ltr" >
<head>
<meta charset="utf-8" />
<title>SOY CMS2</title>
<?php soy2html_layout_include("parts/tag_head.php"); ?>
</head>
<body>
<div id="container">
	
	<div id="header">
		<?php soy2html_layout_include("parts/header.php"); ?>	
	</div>
	<!--  // #header -->
	

	
	<div id="contents">
	
		<div id="cms-menu">
			<div id="cms-menu-body"> 
				
				<?php $app->printMenus(); ?>
				<?php soy2html_layout_include("parts/menu.php"); ?>
			
			</div>
			<!--  // #cms-menu-body -->
			
			<div id="cms-menu-btn">
				<img src="/soycms/common/img/cms-menu-btn.gif" alt="パネルを閉じる" width="12" height="80" />
			</div>
		</div>
		<!--  // #cms-menu -->
			
		<div id="main">
			
			<?php soy2html_layout_include("parts/status.php"); ?>
		
			<div id="main-contents">
				<div id="main-window1" class="main-window"> 
					
					<div class="window-title"> 
						<h2><?php echo $app->getProperty("name"); ?></h2> 
					</div> 
					 
					<div class="window-content"> 
						<?php if(count($app->getTabs())){ ?>
						<div class="tab-menu"> 
							<ul class="tab-index">
								<?php $app->printTabs(); ?>
							</ul>
						</div>
						<?php } ?>
						
						<div class="section">
							<?php echo $html; ?>
						</div>
					</div>
				</div><!-- // #main-window -->
			</div>
			<!--  // #main-contents -->
						
			
		</div>
		<!--  // #main -->
		
	</div>
	<!-- // #contents -->
	

	<div id="footer">
		<?php soy2html_layout_include("parts/footer.php"); ?>
	</div>
	<!--  // #footer -->
	
</div>
<!--  // #container -->	
</body>
</html>
