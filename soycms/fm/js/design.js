/// パネル開閉
// #cms-menu全体
$(function(){

	$("#cms-menu-btn").click(function(){
		 $("#container").toggleClass("hide");

		if ($.cookie("SoyHidePanel")) {
			$.cookie("SoyHidePanel", '', { expires: -1 });
		} else {  
			$.cookie("SoyHidePanel", '1', {expires:7});
		}
	});

	if ($.cookie("SoyHidePanel")) {
		$("#container").addClass("hide");
	} else {  
		$("#container").removeClass("hide");
	}  

});


//#cms-menu各メニュー
$(function(){

	$("#cms-menu .section").each(function (i){
		if ($.cookie("SoyHideMenu")) {
			if (($.cookie("SoyHideMenu").indexOf(i)) != -1) {
				$("#cms-menu .section").eq(i).addClass("hide");
			} else {
				$("#cms-menu .section").eq(i).removeClass("hide");
			}
		}else{
			$("#cms-menu .section").removeClass("hide");
		}
		i = i+1;
	});
	
	$("#cms-menu .title p.btn").click(function(){

		var index = $("#cms-menu .title p.btn").index(this);
		var name = "SoyHideMenu" ;
		var cookVal = $.cookie(name);
		
		$("#cms-menu .section").eq(index).toggleClass("hide");
		
		if ($.cookie(name)) {
			if ((cookVal.indexOf(index)) != -1) {
				if (($.cookie(name).length) == 1) {
					$.cookie(name,null);
				}else{
					var cookVal = $.cookie(name).replace(index,"");
					$.cookie(name,cookVal,{expires:7});
				}
			}else{
				$.cookie(name,cookVal+index,{expires:7});
			}
		}else{
			 $.cookie(name,index,{expires:7});
		}
		
	})
});


// WidgetManager
$(function(){

	$(".widget_item_manager_wrap .cell").each(function (i){
		if ($.cookie("SoyHideWidgetManager")) {
			if (($.cookie("SoyHideWidgetManager").indexOf(i)) != -1) {
				$(".widget_item_manager_wrap .cell").eq(i).addClass("hide");
			} else {
				$(".widget_item_manager_wrap .cell").eq(i).removeClass("hide");
			}
		}else{
			$(".widget_item_manager_wrap .cell").removeClass("hide");
		}
		i = i+1;
	});
	
	$(".widget_item_manager_wrap .cell-title-btn .close").click(function(){

		var index = $(".widget_item_manager_wrap .cell-title-btn .close").index(this);
		var name = "SoyHideWidgetManager" ;
		var cookVal = $.cookie(name);
		
		$(".widget_item_manager_wrap .cell").eq(index).toggleClass("hide");
		
		if ($.cookie(name)) {
			if ((cookVal.indexOf(index)) != -1) {
				if (($.cookie(name).length) == 1) {
					$.cookie(name,null);
				}else{
					var cookVal = $.cookie(name).replace(index,"");
					$.cookie(name,cookVal,{expires:7});
				}
			}else{
				$.cookie(name,cookVal+index,{expires:7});
			}
		}else{
			 $.cookie(name,index,{expires:7});
		}
		
	})
});

/// テーブルソート
$(function(){
	$("table.list-table > tbody > tr:even").addClass("odd");
});

////IE6 PNG
if (typeof document.documentElement.style.maxHeight != "undefined") {
}
else {
DD_belatedPNG.fix('#site-id h1 img,#cms-menu-help,#cms-menu-help p,#user-status,.window-content .tab-menu ul.tab-index li,.window-content .tab-menu ul.tab-index li a,p.close,.colorpicker_color div'); //適用させる要素,id,class名
}


//// IE6 a:hover
try { 
						document.execCommand('BackgroundImageCache', false, true);
						} catch(e) {}