<!DOCTYPE html>
<html lang="ja" dir="ltr" >
<head>
<meta charset="utf-8" />
<title>SOY CMS2</title>
<?php soy2html_layout_include("parts/tag_head.php"); ?>
</head>
<body class="layout-1c">
<div id="container">
	
	<div id="contents" style="">
			
		<div id="main">
			
			<div id="main-contents">
				<?php echo $html; ?>
			</div>
			<!--  // #main-contents -->
						
			
		</div>
		<!--  // #main -->
		
	</div>
	<!-- // #contents -->
	
</div>
<!--  // #container -->	
<script type="text/javascript">
$(function(){
	setInterval(function(){
		if(window.parent){
			$(window.parent.document).find("iframe.frame").height($(document).height());
		}
	},500);
	
	$(window.parent).scrollTop(0);
});
</script>
<style type="text/css">
html,body{
	overflow:hidden;
	overflow-y:hidden;
}
#contents{
	padding:0;
	margin:0;
}
#contents #main-contents{
	margin:0;
	padding:0;
}
#main-contents .main-window{
	margin:0;
}
</style>
</body>
</html>
