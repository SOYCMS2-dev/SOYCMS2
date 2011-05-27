<!DOCTYPE html>
<html lang="ja" dir="ltr" >
<head>
<meta charset="utf-8" />
<title>SOY CMS2</title>
<?php soy2html_layout_include("parts/tag_head.php"); ?>
</head>
<body>
<div id="container">
	
	<div id="common_popup_status" style="display:none;">
		<span class="user_name" onclick="$('#common_popup_status .user_detail').toggle();"></span>
		<div class="user_detail" style="display:none;"></div>
	</div>
	
	<div id="header">
		<?php soy2html_layout_include("parts/header.php"); ?>	
	</div>
	<!--  // #header -->
	

	
	<div id="contents">
	
		<div id="cms-menu">
			<div id="cms-menu-body"> 
			
				<?php soy2html_layout_include("parts/menu.php"); ?>
			
			</div>
			<!--  // #cms-menu-body -->
			
			<div id="cms-menu-btn" title="パネルを閉じる"> 
				<span title="パネルを開く"></span> 
			</div> 
		</div>
		<!--  // #cms-menu -->
			
		<div id="main">
			
			<?php soy2html_layout_include("parts/status.php"); ?>
		
			<div id="main-contents">
				<?php echo $html; ?>
			</div>
			<!--  // #main-contents -->
			
			<?php
				if(method_exists($this,"getSubMenu")){
					$this->getSubMenu();
				} 
			?>
			
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
