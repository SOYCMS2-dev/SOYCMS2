$(function(){
	
	
	adjust_height();
	
	$("#files tbody tr:even").addClass("odd");
	
	
});

var adjust_height = function(mode,_w){
	//adjust iframe size
	if(!_w)_w = window.parent;
	min_height = 800;
	if(_w){
		var d = _w.parent.document;
		frame = $(d).find("#manager_fr");
		frame_d = frame[0].contentWindow.document;
		
		if (mode) {
			
			var height = frame.height();
			height = Math.max(min_height,height);
			
			$("body",frame_d).height(height);
			$("body",frame_d).css("overflow", "auto");
			$("#files",frame_d).width("99%");
		} else {
			height = $("body",frame_d).height();
			var inner_height = $("#toolbar",frame_d).height() + $("#files",frame_d).height();
			if(height < inner_height){
				height = inner_height;
			}
			if(height < min_height){
				$("body",frame_d).height(min_height);
				height = min_height;
			}
			
			$(d).find("#manager_fr").css("height", height);
			
		}
	}
}

var read_more_files = function(ele){
	
	var url = $(ele).attr("href");
	$(ele).append($("<div class='loading'></div>"));
	
	$.get( url, function(response){
		$("#more_link_row").fadeOut("fast",function() { 
			$(this).remove(); 
		
			$("#files tbody").append(response);
			$("#files tbody tr:even").addClass("odd");
			
			if ($("body").height() > 300) {
				adjust_height();
			}
			fm_prepare();
		});
	});
	
	return false;
}

var common_open_editor = function(url){
	url = SOYCMS_ROOT_URL + "fm/FileManager/Editor?url=" + url;
	return common_open_top_layer(url);
}

var common_open_top_layer = function(url){
	_w = (window.top) ? window.top : window;
	_d = _w.document;
	
	iframe = _d.createElement("iframe");
	$(iframe).attr("src",url).attr("frameborder","none").attr("id","common_open_editor");
	content = $("<div></div>",_d).css("height","99%").attr("id","common_open_editor_wrapper");
	content.append(iframe);
	
	
	$("#popupLayer",_d).show()
	$("#overLayer-L .popup-inner",_d).append(content);
	$("#overLayer-L",_d).show().css({
		marginTop:"-"+$("#overLayer-L",_d).height()/2+"px" , 
		marginLeft:"-"+$("#overLayer-L",_d).width()/2+"px"  
	});
	
	$("#popupLayer,#overLayer p.close,#overLayer-L p.close",_d).click(function(){
		content.remove();
		adjust_height();
	});
	
}
var common_close_editor = function(){
	_w = (window.top) ? window.top : window;
	_d = _w.document;
	
	$("#common_open_editor_wrapper",_d).remove();
	$("#popupLayer",_d).hide();
	$("#overLayer",_d).hide();
	$("#overLayer-L",_d).hide();
	
	adjust_height(true,_w);
}

var common_click_dir = function(url){
	if(window.parent){
		window.parent.location.hash = url;
	}
}

/* popup */

/* popup */
$(function(){
			
	$(".popup-content").hide();
	$("body").append("<div id='popupLayer'></div><div id='overLayer'></div><div id='overLayer-L'></div>");
	$("#overLayer").html("<p class='close' title='Close'></p><div class='popup-inner'></div>");
	$("#overLayer-L").html("<p class='close' title='Close'></p><div class='popup-inner'></div>");
	
	var content = null;
	
	var close_poup = function(){
		$("#popupLayer").hide();
		$("#overLayer").hide();
		$("#overLayer-L").hide();
		
		if (content) {
			$("body").append(content);
			content.hide();
		}else{
			$("#overLayer .popup-inner").html("");
			$("#overLayer-L .popup-inner").html("");
		}
	};
	
	$("#popupLayer,#overLayer p.close,#overLayer-L p.close").click(function(){
		close_poup();	
	});
	
	$(".popup-btn").click(function(){
		$("#popupLayer").show()
		content = $($(this).attr("href")).show();
		$("#overLayer .popup-inner").append(content);
		$("#overLayer").show().css({
			marginTop:"-"+$("#overLayer").height()/2+"px" , 
			marginLeft:"-"+$("#overLayer").width()/2+"px"  
		});
		
		return true;
	});
	
	$(".popup-btn-L").click(function(){
		$("#popupLayer").show()
		content = $($(this).attr("href")).show();
		$("#overLayer-L .popup-inner").append(content);
		$("#overLayer-L").show().css({
			marginTop:"-"+$("#overLayer-L").height()/2+"px" , 
			marginLeft:"-"+$("#overLayer-L").width()/2+"px"  
		});
		return true;
	});
	
	
	
	
	if($.browser.msie && $.browser.version<7){
		$(window).scroll(function(){
			$("#popupLayer").get(0).style.setExpression("top","$(document).scrollTop()+'px'");
			$("#overLayer").get(0).style.setExpression("top","($(document).scrollTop()+$(window).height()/2)+'px'");
			$("#overLayer-L").get(0).style.setExpression("top","($(document).scrollTop()+$(window).height()/2)+'px'");
		});
	}
	
});

/* filemanager context menu */
$(function(){
	
	fm_prepare();
	
	$(document).bind("click",function(){
		$("#fm_contextmenu").hide();
	});
	$("#fm_contextmenu a").bind("click",function(){
		$("#fm_contextmenu").hide();
	});
});

function fm_prepare(){
	$(".file_row").unbind("contextmenu").bind("contextmenu",function(e){
		title = $(this).find(".url_link").attr("title");
		_top = $(this).offset().top + 20;
		_left = e.pageX;
		
		name = title.replace(/.*?([^\/]+)\/?$/,'$1');
		
		$("#old_name").val(name);
		$("#new_name").val(name);
		$("#remove_file").val(name)
		
		$(".select_file_name").html(name);
		
		$("#fm_contextmenu").show().css({
			top : _top,
			left : _left
		});
		
		e.preventDefault();
		return false;
	});
}
