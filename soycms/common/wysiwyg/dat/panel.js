//
$(function(){
	$.fn.toggleFade = function(){
		return this.each(function(){
			if($(this).is(":hidden")){
				$(this).fadeIn();				
			}else{
				$(this).fadeOut();
			}
		});
	};
	
	$('input.input-link')
			.focus(function() { $(this).addClass("active") })
			.blur(function() { if ($(this)[0].value == '') { $(this).removeClass("active") } });
	
	
	$(window.document).click(function(){
		
	});
	
});

var _hideallpopup = function(){
	
	$(".downpanel-layer,.downmenu-layer").hide();
	$(".downpanel-fix").hide();
	$(".panel-parts").css("zIndex",50);	//for ie bug
	
};

var _showpopup = function(ele){
	return $(ele).show() 
		.find(".panel-parts").css("zIndex",2000000);
	return $(ele);
};

var _showoption = function(ele){
	var option = $("#" + $(ele).parents(".downpanel").attr("id") + "_option");
	
	is_visible = option.is(":visible");
	
	_hideallpopup();
	
	if (is_visible && !aobata_editor.is_IE) {
		return false;
	}
	
	//表示する
	$(ele).parents(".panel-parts").css("zIndex",2000000);
	
	_showpopup(option).css({
		left : 0,
		top : 29
	});
	
};

var _downallow = function(){
	
	$(".downmenu-layer").bind("click",function(event){
		$(this).toggleFade();
	});
	
	$(".toggle-btn").bind("click",function(){
		$(this).toggleClass("active");
	});
	
}
