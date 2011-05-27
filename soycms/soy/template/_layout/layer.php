<!DOCTYPE html>
<html lang="ja" dir="ltr" >
<head>
<meta charset="utf-8" />
<title>SOY CMS2</title>
<?php soy2html_layout_include("parts/tag_head.php"); ?>
</head>
<body class="layout-1c" style="width:680px;min-width:680px;">
<div id="container" style="width:99%;">
	
	<div id="contents">
			
		<div id="main">
			
			<?php soy2html_layout_include("parts/status.php"); ?>
		
			<div id="main-contents" style="width:99%;">
				<?php echo $html; ?>
			</div>
			<!--  // #main-contents -->
						
			
		</div>
		<!--  // #main -->
		
	</div>
	<!-- // #contents -->
	
</div>
<!--  // #container -->	

<style type="text/css">
.pagelist-menu{
	display:none;
}
.crumbs{
	display:none;
}
</style>
<script type="text/javascript">
$(function(){
	$("a").each(function(){
		href = $(this).attr("href");
		if(href.length > 0 && href.indexOf("layer") < 0 && !href.match(/^javascript:/)){
			if(href.indexOf("?") < 0)href += "?";
			href += "layer";
			$(this).attr("href",href);
		}
	});
});
</script>
</body>
</html>
