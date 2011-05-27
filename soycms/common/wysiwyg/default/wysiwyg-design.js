////IE6 PNG
if (typeof document.documentElement.style.maxHeight != "undefined") {
}
else {
DD_belatedPNG.fix('#panel-layers .property-panel .panel-header,#panel-layers .property-panel .panel-header .panel-close,.colorpicker_color div'); 
}

///

$(function(){

	$('input.input-link')
			.focus(function() { $(this).addClass("active") })
			.blur(function() { if ($(this)[0].value == '') { $(this).removeClass("active") } });


});

