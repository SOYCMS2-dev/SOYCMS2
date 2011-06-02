/**
 * 新しい要素を追加
 * @param {Object} ele
 * @param {Object} _section
 * @param {Object} _snippet
 */
function append_new_section(ele,_section,_snippet,_form){
	
	var url = EDITOR_ACTION_URL;
	var section_list = $(ele).parents(".section_list");
	var replace = (_form) ? $.map($("input,textarea,select",_form),function(n,i){
		if($(n).attr("name").length < 1)return null;
		if(!$(n).val())return $(n).attr("name") + "=";
		return $(n).attr("name") + "=" + $(n).val().replace(/&/g,'%26');
	}).join("&") : null;
	var count = $(".section_list").size();
	
	_hideallpopup();
	$("#append_new_section").hide();
	
	$.post(url,{
		section : _section,
		snippet : _snippet,
		key : count,
		values : replace
	},function(html){
		
		var node = $(html);
		section_list.after(node);
		
		//大きさの設定
		$("#main-contents").css("height","");
		
		if(_section == "wysiwyg" || _section.match(/_editable/)){
			editor = new aobata_editor($("textarea",node)[0]);
		}else{
			editor = new aobata_editor($("textarea",node)[0],{editable:false});
		}
		
		editor.adjustSize(true);
		
		entry_editor_page_prepare();
		$(window).scrollTop(editor.frame.offset().top - 180);
		
		$(".aobata_editor_appended").removeClass("aobata_editor_appended");
		editor.doActive(true);
		editor.wrapper.addClass("aobata_editor_appended");
		
	});
	
	
}

/**
 * insert_new_section
 * @param {Object} ele
 * @param {Object} _section
 * @param {Object} _snippet
 */
function insert_new_section(ele,_section,_snippet,_form){
	
	_hideallpopup();
	
	var url = EDITOR_ACTION_URL;
	var replace = (_form) ? $.map($("input,textarea,select",_form),function(n,i){
		if($(n).attr("name").length < 1)return null;
		return $(n).attr("name") + "=" + $(n).val().replace(/&/g,'%26');
	}).join("&") : null;
	
	$.post(url,{
		section : _section,
		snippet : _snippet,
		values : replace,
		mode : "content"
	},function(html){
		editor = aobata_editor.get();
		
		if(aobata_editor.is_IE && editor.caret){
			editor.caret.select();
		}
		
		editor.insertHTML(html);
		editor.adjustSize(true);
	});
	
}

/**
 * セクションのフォームを表示
 */
function show_new_section_form(id){
	
	_hideallpopup();
	
	form = $("#" + id + "_section_form");
	
	form.find("input,textarea,select").val("");	//clear all values
	
	try {
		parentEle = form.parents().filter(".panel-parts");
	}catch(e){
		form.show();
		return;
	}
	
	//右過ぎる
	pos = form.width() + parentEle.position().left;
	$(parentEle).css("zIndex",51);
	
	if (pos >= $(window).width()) {
		form.show().css({
			right : 0
		});
	} else {
		form.show().css({
			left : 0
		});
	}
	
	
	
	return false;
}

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

/**
 * 挿入エリアの表示
 */
function show_insert_lines(ele){
	$(ele).parents(".article-header").append($("#append_new_section"));
	_hideallpopup();	//隠す
	
	$("#append_new_section").slideToggle();
}


/**
 * 初期化
 */
function entry_editor_page_prepare(){
	$(".btn-orderup").unbind("click").click(function(){
		SectionController.moveUp($(this));
	});
	
	$(".btn-orderdown").unbind("click").click(function(){
		SectionController.moveDown($(this));
	});
	
	$(".btn-delelement").unbind("click").click(function(){
		SectionController.remove($(this));
	});
	
	$(".undo-remove").unbind("click").click(function(){
		SectionController.cancelRemove($(this));
	});
	
	$(".close-editor").unbind("click").click(function(){
		if(aobata_editor.active)aobata_editor.active.doActive(false);
	});
}

/**
 * open attachments
 */
function entry_editor_show_attachments(func){
	$("#show_attachment_btn").click();
	prepare_attachment(func);
}

/**
 * open insert popup
 */
function show_insert_popup(ele){
	_hideallpopup();
	aobata_editor.paramView.hide();
	
	$(".popup-addelement-line").toggle().css({
		top : $(ele).offset().top - 100,
		left : "50%", 
		marginLeft:"-"+$(".popup-addelement-line").width()/2+"px"  
	}).draggable({
		handle : ".panel-header",
		cursor : "crosshair",
		start : function(){
			$("iframe").hide();
		},
		stop: function(event, ui){
			$("iframe").show();
	    }
	});
}


$(function(){
	if(SOYCMS_SITE_URL.indexOf("/" + location.host + "/") < 0){
		aobata_editor.option.base_url = SOYCMS_SITE_URL;
	}
	
	entry_editor_page_prepare();
	
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

var SectionController = {
	
	moveUp : function(ele){
		
		aobata_editor.syncAll();
		
		var parent = this.getEl(ele);
		
		tgt1 = parent;
		tgt2 = parent.prev(".section_list");
		if(tgt2.size()<1)return;
		
		aobata_editor.close();
		
		$(window).scrollTop(tgt2.offset().top - 50);
		tgt2.before(parent);
		this.rebuild();
		
		return;
	},
	
	moveDown : function(ele){
		aobata_editor.syncAll();
		
		var parent = this.getEl(ele);
		
		tgt1 = parent;
		tgt2 = parent.next(".section_list");
		if(tgt2.size()<1)return;
		
		aobata_editor.close();
		
		$(window).scrollTop(tgt1.offset().top + tgt2.height() - 50);
		tgt2.after(parent);
		this.rebuild();
		
		
		this.rebuild();
		
	},
	
	remove : function(ele){
		this.getEl(ele).find(".article-body").css("opacity","0.5");
		this.getEl(ele).addClass("section_removed");
		this.getEl(ele).find(".section_remove").val(1);
	},
	
	cancelRemove : function(ele){
		this.getEl(ele).find(".article-body").css("opacity","1");
		this.getEl(ele).removeClass("section_removed");
		this.getEl(ele).find(".section_remove").val(0);
	},
	
	getEl : function(ele){
		$("#sections_container > p").remove();
		return $(ele).parents(".section_list");
	},
	
	rebuild : function(){
		//last section's footer is always visible
		$(".last-section").removeClass("last-section");
		$(".article-footer").hide();
		$(".section_list:last").addClass("last-section");
		//$(".last-section .article-footer").show();
	}
	
	
};

/*
 * custom field controller
 */
var FieldContoller = {
	remove : function(ele){
		
	},
	
	prepare : function(section){
		ele = section.find(".field-form");
		
			section.find(".field-remove-btn").click(function(){
				$(this).parents(".field-form").addClass("field-form-deleted")
					.find("input,select,textarea").attr("disable","disable");
			});
			section.find(".field-cancel-btn").click(function(){
				$(this).parents(".field-form").removeClass("field-form-deleted")
					.find("input,select,textarea").removeAttr("disable");
			});
		
		section.find(".field-append-btn").click(function(){
			section.find(".field-template").before($("<div class='field-form'></div>").append(
				section.find(".field-template").clone().html()
					.replace(/#INDEX#/g,section.find(".field-form").size())
			));
		});
	}
};
$(function(){
	$(".field-section").each(function(){
		FieldContoller.prepare($(this));
	});
	$(".image-input").each(function(index){
		var btn = $('<a class="s-btn" href="javascript:void(0);">参照</a>');
		btn.click(function(){
			var target = $(this).prev();
			entry_editor_show_attachments(function(img,link){
				target.val(img);
				$("#" + target.attr("id") + "_img").attr("src",img);
			});
		});
		$(this).after(btn);
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

