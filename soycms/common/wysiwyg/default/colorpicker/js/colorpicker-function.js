$(function(){
	$('#colorpickerHolder').ColorPicker({flat: true});
});

$(function(){
	$('.colorSelector').ColorPicker({
	color: '#0000ff',
	onShow: function (colpkr) {
		$(colpkr).fadeIn(100);
		return false;
	},
	onHide: function (colpkr) {
		$(colpkr).fadeOut(100);
		return false;
	},
	onChange: function (hsb, hex, rgb) {
		$('.colorSelector span').css('backgroundColor', '#' + hex);
	}
});
});
