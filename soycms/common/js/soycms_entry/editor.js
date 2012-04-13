

/**
 * URIの保存
 */
function save_entry_uri(ele){
	
	var url = EDITOR_ACTION_URL;
	common_start_loading(ele);
	
	$.post(url,{
		uri : $("#entry_uri_input").val()
	},function(html){
		common_stop_loading();
		
		if (html != "0") {
			$("#entry_url").show();
			$("#entry_link").html(html);
			$("#entry_url_edit").hide();
		}else{
			alert("invalid!");
			$("#entry_uri_input").focus();
		}
	});
		
}

/**
 * 自動保存の設定の保存
 */
function save_autosave_config(ele){
	
	var url = EDITOR_ACTION_URL;
	
	$.post(url,{
		save_autosave_config : ($("#autosave").attr("checked") ? 1 : 0)
	},function(html){
		
	});
	
}

/**
 *  サムネイル作成の設定の保存
 */
function save_make_thumbnail_config(ele){
	
	var url = EDITOR_ACTION_URL;
	$.post(url,{
		make_thumbnail_config : ($("#create_thumbnail").attr("checked") ? 1 : 0),
		thumbnail_size_l : $("#thumbnail_size_l").val(),
		thumbnail_size_m : $("#thumbnail_size_m").val(),
		thumbnail_size_s : $("#thumbnail_size_s").val()
	},function(html){
		
	});
}

/**
 *  リサイズ設定の保存
 */
function save_auto_resize_config(ele){
	
	var url = EDITOR_ACTION_URL;
		
	$.post(url, {
		resize_auto_config: ($("#resize_auto").attr("checked") ? 1 : 0),
		resize_auto_width: $("#resize_auto_width").val(),
		resize_auto_height: $("#resize_auto_height").val()
	}, function(html){
	});
	
}

/**
 * 今すぐ保存
 */
function save_now(flag){
	
	if(aobata_editor.editors.length < 1){
		return;
	}
	
	aobata_editor.saving = true;
	
	common_start_loading($("#last_update_date"));
	var action = $("#entry_form").attr("action");
	if(action.indexOf("?") < 0)action += "?"; 
	
	//aobata_editor
	aobata_editor.syncAll();
	
	$.post(
		action + "&autosave",
		$("#entry_form").serialize(),
		function(html){
			common_stop_loading();
			
			aobata_editor.changed = false;
			aobata_editor.saving = false;
			
			$("#last_update_date").html(html)
				.css({
					"backgroundColor": "yellow",
					"fontWeight" : 900
				})
				.animate({ backgroundColor: 'white', color: 'black'},
						{
							complete :  function(){
								$("#last_update_date").css("fontWeight",500);
							},
							duration : "slow"
						}
				);
		}
	);
}

/**
 * 作成日時の保存
 */
function save_create_date(ele){
	
	var url = EDITOR_ACTION_URL;
	common_start_loading(ele);
	
	$.post(url,
		$("#create_date_form input").serialize() + "&save_create_date=1",
		function(html){
			common_stop_loading();
			$("#create_date_text").html(html).parent().show();
			$("#create_date_form").hide();
		}
	);
	
}

/**
 * 作成者名の保存
 */
function save_author(ele){
	
	var url = EDITOR_ACTION_URL;
	common_start_loading(ele);
	
	$.post(url,{
		author : $("#author_input").val(),
		author_link_text : $("#author_link_text").val(),
		author_link_url : $("#author_link_url").val(),
		flag : ($("#entry_author_check").attr("checked")) ? 0 : 1
	},function(html){
		common_stop_loading();
		$("#author_text").html($("#author_input").val());
		$("#author_info").show();
		$("#author_edit").hide();
	});
		
}

/**
 * 履歴を表示
 * @param {Object} ele
 */
function show_hisotry_list(query){
	var url = EDITOR_HISTORY_URL;
	
	if(query)url = query;
	
	$("#history_popup").load(url);
}

/**
 * メモを保存
 */
function save_memo(ele){
	
	var url = EDITOR_ACTION_URL;
	common_start_loading(ele);
	
	$.post(url,{
		memo : $("#entry_memo").val()
	},function(html){
		common_stop_loading();
		$(".save_memo_comment").remove();
		$(ele).after('<span class="save_memo_comment">'+html+'</span>');
	});
		
}

/**
 * タグを保存
 */
function save_tags(ele){
	
	var url = EDITOR_ACTION_URL;
	common_start_loading(ele);
	
	$.post(url,{
		tags : $("#entry_tags").val()
	},function(html){
		common_stop_loading();
		$("#tag_list").html(html);
		$("#tag_edit").hide();
	});
		
}

/**
 * キーワード Descriptionの保存
 * @param {Object} element
 */
function save_meta(ele){
	var url = EDITOR_ACTION_URL;
	
	common_start_loading(ele);
	
	$.post(url,{
		keyword : $('#entry_keyword').val(),
		description : $('#entry_description').val()
	},function(html){
		common_stop_loading();
		$(".save_meta_comment").remove();
		$(ele).after('<span class="save_meta_comment">'+html+'</span>');
	});
}

/**
 * ラベルを保存
 * @param {Object} element
 */
function save_label(ele){
	var url = EDITOR_ACTION_URL;
	
	common_start_loading(ele);
	
	var labels = [];
	
	$(".label_wrap input:checked").each(function(){
		labels.push($(this).val());
	});
	
	$.post(url,{
		labels : labels
	},function(html){
		common_stop_loading();
	});
}

/**
 * コメント投稿、トラックバック投稿・更新ピングのオン・オフ
 */
function save_comment_trackback(ele){
	var url = EDITOR_ACTION_URL;
	
	common_start_loading(ele);
	
	$.post(url,{
		comment : ($("#allow_comment").attr("checked")) ? 1 : 0
		,trackback : ($("#allow_trackback").attr("checked")) ? 1 : 0
		,send_ping : ($("#send_ping").attr("checked")) ? 1 : 0
		,feed_entry : ($("#feed_entry").attr("checked")) ? 1 : 0
	},function(html){
		common_stop_loading();
	});
}

/**
 * トラックバックを送信する
 * @param {Object} element
 */
function send_trackback(ele){
	var url = EDITOR_ACTION_URL;
	
	common_start_loading(ele);
	
	$.post(url,{
		send_trackback : true
		,destination : $("#trackback_destination").val()
	},function(html){
		common_stop_loading();
		$(".send_trackback_comment").remove();
		$(ele).after('<p class="send_trackback_comment">'+html+'</p>');
	});
}

/**
 * 公開期間を保存
 * @param {Object} element
 */
function save_open_period(ele){
	var url = EDITOR_ACTION_URL;
	
	common_start_loading(ele);
	
	obj = {
		save_open_period : true
	};
	$("#openperiod_config input.date-input,#openperiod_config input.time-input,").each(function(index,ele){
		obj[$(ele).attr("name")] = $(ele).val();
	});
	
	$.post(url,obj,function(html){
		common_close_popup();
		$("#openperiod_text").html(html);
		common_stop_loading();
	});
}

/**
 * urlのうち [XXXX].html だけを反転する
 * @param {Object} element
 */
function focus_url(element){
	var value = $(element).val(),startCharNo=0,endCharNo=value.length;
	if(value.match(/\.html?$/)){
		endCharNo -= 5;
	}
	
	if(element.setSelectionRange)
	   element.setSelectionRange(startCharNo, endCharNo);
	else {
	   var r = element.createTextRange();
	   r.collapse(true);
	   r.moveEnd('character', endCharNo);
	   r.moveStart('character', startCharNo);
	   r.select();   
	}
}

$(function(){
	if(SOYCMS_SITE_URL.indexOf("/" + location.host + "/") < 0){
		aobata_editor.option.base_url = SOYCMS_SITE_URL;
	}
	
	$("#main-contents").css("height","");
	
	$("#save_submit_btn").click(function(){
		aobata_editor.changed = false;
	});
	
	$(window).bind("beforeunload",function(event){
		if(aobata_editor.is_IE){
			return;
		}
		
		if(aobata_editor.saving){
			return;
		}
		
		if (aobata_editor.changed) {
			return "他の画面に遷移しようとしています。保存していない内容は破棄されます。";
		}
	});
	
	//autosave every minute
	setInterval(function(){
		if($("#autosave:checked").size() > 0){
			save_now();	
		}
	},300000)
});



/*
 * custom field controller
 */
var FieldContoller = {
	remove : function(ele){
		alert(ele);
	},
	
	prepare : function(section){
		ele = section.find(".field-form");
		
			section.find(".field-remove-btn").click(function(){
				ele =  $(this).parents(".field-form").addClass("field-form-deleted")
					.find("input,select,textarea").attr("disable","disable");
				ele.attr("name",ele.attr("name").replace(/^EntryCustomField/,"_EntryCustomField")).slideUp();
			});
			section.find(".field-cancel-btn").click(function(){
				ele = $(this).parents(".field-form").removeClass("field-form-deleted")
					.find("input,select,textarea").removeAttr("disable");
				ele.attr("name",ele.attr("name").replace(/^_EntryCustomField/,"EntryCustomField")).slideDown();
			});
	},
	
	append : function(ele, max){
		var section = $(ele).parents(".field-section");
		if(max > 0 && section.find(".field-form").size() > max){
			retrun;
		}
		
		section.find(".field-template").before($("<div class='field-form'></div>").append(
			section.find(".field-template").clone().html()
				.replace(/#INDEX#/g,section.find(".field-form").size())
		));
	}
};
$(function(){
	$(".field-section").each(function(){
		FieldContoller.prepare($(this));
	});
	$(".image-input").each(function(index){
		var btn = $('<a class="s-btn" href="javascript:void(0);">参照</a>')
			.addClass("show-attachment-btn");
		
		$(this).after(btn);
	});
	$(".show-attachment-btn").live("click",function(){
		var target = $(this).prev();
		aobata_editor.show_attachments(function(img,link){
			target.val(img);
			$("#" + target.attr("id") + "_img").attr("src",img);
		});
	});
});



/*
 * jQuery Color Animations
 * Copyright 2007 John Resig
 * Released under the MIT and GPL licenses.
 */

(function(jQuery){

	// We override the animation for all of these color styles
	jQuery.each(['backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'color', 'outlineColor'], function(i,attr){
		jQuery.fx.step[attr] = function(fx){
			if ( fx.state == 0 ) {
				fx.start = getColor( fx.elem, attr );
				fx.end = getRGB( fx.end );
			}

			fx.elem.style[attr] = "rgb(" + [
				Math.max(Math.min( parseInt((fx.pos * (fx.end[0] - fx.start[0])) + fx.start[0]), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[1] - fx.start[1])) + fx.start[1]), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[2] - fx.start[2])) + fx.start[2]), 255), 0)
			].join(",") + ")";
		}
	});

	// Color Conversion functions from highlightFade
	// By Blair Mitchelmore
	// http://jquery.offput.ca/highlightFade/

	// Parse strings looking for color tuples [255,255,255]
	function getRGB(color) {
		var result;

		// Check if we're already dealing with an array of colors
		if ( color && color.constructor == Array && color.length == 3 )
			return color;

		// Look for rgb(num,num,num)
		if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
			return [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];

		// Look for rgb(num%,num%,num%)
		if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
			return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];

		// Look for #a0b1c2
		if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
			return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];

		// Look for #fff
		if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
			return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];

		// Otherwise, we're most likely dealing with a named color
		return colors[jQuery.trim(color).toLowerCase()];
	}
	
	function getColor(elem, attr) {
		var color;

		do {
			color = jQuery.curCSS(elem, attr);

			// Keep going until we find an element that has color, or we hit the body
			if ( color != '' && color != 'transparent' || jQuery.nodeName(elem, "body") )
				break; 

			attr = "backgroundColor";
		} while ( elem = elem.parentNode );

		return getRGB(color);
	};
	
	// Some named colors to work with
	// From Interface by Stefan Petre
	// http://interface.eyecon.ro/

	var colors = {
		aqua:[0,255,255],
		azure:[240,255,255],
		beige:[245,245,220],
		black:[0,0,0],
		blue:[0,0,255],
		brown:[165,42,42],
		cyan:[0,255,255],
		darkblue:[0,0,139],
		darkcyan:[0,139,139],
		darkgrey:[169,169,169],
		darkgreen:[0,100,0],
		darkkhaki:[189,183,107],
		darkmagenta:[139,0,139],
		darkolivegreen:[85,107,47],
		darkorange:[255,140,0],
		darkorchid:[153,50,204],
		darkred:[139,0,0],
		darksalmon:[233,150,122],
		darkviolet:[148,0,211],
		fuchsia:[255,0,255],
		gold:[255,215,0],
		green:[0,128,0],
		indigo:[75,0,130],
		khaki:[240,230,140],
		lightblue:[173,216,230],
		lightcyan:[224,255,255],
		lightgreen:[144,238,144],
		lightgrey:[211,211,211],
		lightpink:[255,182,193],
		lightyellow:[255,255,224],
		lime:[0,255,0],
		magenta:[255,0,255],
		maroon:[128,0,0],
		navy:[0,0,128],
		olive:[128,128,0],
		orange:[255,165,0],
		pink:[255,192,203],
		purple:[128,0,128],
		violet:[128,0,128],
		red:[255,0,0],
		silver:[192,192,192],
		white:[255,255,255],
		yellow:[255,255,0]
	};
	
})(jQuery);

