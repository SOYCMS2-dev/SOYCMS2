var is_ie = /*@cc_on!@*/false;

/* tab */
$(function(){
	
	var hash = location.hash;
	var jump = null;
	if (hash.indexOf("/") > 0) {
		jump = hash.substring(hash.indexOf("/") + 1);
		hash = hash.substring(0, hash.indexOf("/"));
	}
	
	if(!hash){
		if (($(hash + "_contents").size() < 1) && $(".tab-contents li").size() > 0) {
			var id = $(".tab-contents li")[0].getAttribute("id");
			if (id && id.length > 0) {
				hash = "#" + id.replace(/_contents$/, "");
			}
		}
	}
	
	if(hash){
		$(".tab-index li").each(function(){
			
			$(this).click(function(){
				
				var hash = $(this).find("a").attr("href");
				
				if ($(".tab-contents li" + hash + "_contents").size() > 0) {
					$(".tab-index li.on").removeClass("on");
					$(this).addClass("on");
				
					$(".tab-contents > li").hide();
					$(".tab-contents li" + hash + "_contents").show();
				}
				
			});
			
			if($(this).find("a").attr("href") == hash){
				$(this).addClass("on");
			}
		});
		
		
		$(".tab-contents > li").hide().each(function(){
			if (("#" + $(this).attr("id")) == hash + "_contents") {
				$(this).show();
			}
		});
		
	}
	
	$(".tab_container > li").hide();
	$(".tab_container li:first-child").show();
	$(".tab_index li:first-child").addClass("on");
	
	var counter = 0;
	$(".tab_index li").each(function(){
		$(this).click(function(index){
			return function() {
				$(".tab_container > li").hide();
				$(".tab_index li.on").removeClass("on");
				$(this).addClass("on");
				$($(".tab_container > li").get(index)).show();
				
			};
		}(counter));
		counter++;
	});
	
	if(jump){
		location.hash = jump;
		location.hash = hash;
	}
	
});


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
		
		return false;
	});
	
	$(".popup-btn-L").click(function(){
		$("#popupLayer").show()
		content = $($(this).attr("href")).show();
		$("#overLayer-L .popup-inner").append(content);
		$("#overLayer-L").show().css({
			marginTop:"-"+$("#overLayer-L").height()/2+"px" , 
			marginLeft:"-"+$("#overLayer-L").width()/2+"px"  
		});
		return false;
	});
	
	
	
	
	if($.browser.msie && $.browser.version<7){
		$(window).scroll(function(){
			$("#popupLayer").get(0).style.setExpression("top","$(document).scrollTop()+'px'");
			$("#overLayer").get(0).style.setExpression("top","($(document).scrollTop()+$(window).height()/2)+'px'");
			$("#overLayer-L").get(0).style.setExpression("top","($(document).scrollTop()+$(window).height()/2)+'px'");
		});
	}
	
});

/* layout */
$(function(){
	if ($("#command-menu").size() < 1) {
		$("body").addClass("layout-2c");
	}
});

$(function(){	
	setTimeout(function(){
		$(".message").fadeOut();
	},3000);
	
	setTimeout(function(){
		$("#user-status").fadeOut();
	},5000);
});

/* sort table */
$(function(){

	$(".up_btn").click(function(){
		var tr = $(this).parents("tr");
		common_sort_table(tr,true);
	});
	
	$(".down_btn").click(function(){
		var tr = $(this).parents("tr");
		common_sort_table(tr,false);
	});
	
	$(".up_btn,.down_btn").click(function(){
		$(".save_order_wrap").fadeIn();
	});


});
var common_sort_table = function(tr,flag){
	
	if(flag){
		tr.prev().insertAfter(tr).find("td *").hide().fadeIn(1000);
	}else{
		tr.next().insertBefore(tr).find("td *").hide().fadeIn(1000);
	}
	
	tr.find("td *").hide().fadeIn(1000);
	
	$("table.list-table tr").removeClass("odd");
	$("table.list-table > tbody > tr:even").addClass("odd");
};

var common_start_loading = function(ele){
	$(ele).after("<span class='loading'></span>");
};
var common_stop_loading = function(){
	$(".loading").remove();
}

/* message */
var soycms_message = function(html){
	$("#user-status-content").html(html);
	$("#user-status").fadeIn();
	setTimeout(function(){
		$("#user-status").fadeOut();	
	},3000);
};

/* textarea */
//TextAreaに関数付加
function advance_text_area(textarea){
	
	//テキストのペースト
	textarea.insertHTML = function(html){
		if (document.selection != null){
			if(!textarea.selection)textarea.selection = document.selection.createRange();
			textarea.selection.text = html;
			textarea.focus();
			textarea.selection.select();
			
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			
			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);
			
			var scroll = textarea.scrollTop;
			var scrollLeft = textarea.scrollLeft;
			
			textarea.value = beforeString + html + afterString;
			
			textarea.scrollTop = scroll;
			textarea.scrollLeft = scrollLeft;
			
			textarea.setSelectionRange(start,start + html.length);
			
			textarea.focus();
		}
	};
	
	//タブの挿入
	textarea.insertTab = function(e){
		
		if (document.selection != null){
			textarea.selection = document.selection.createRange();
			
			var value = textarea.selection.text;
			
			if(textarea.selection.compareEndPoints('StartToEnd',textarea.selection) == 0){		
				textarea.selection.text = String.fromCharCode(9);
			}else{
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 ) + "\n";
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					value = "\t" + value + "\n";
				}
				
				textarea.selection.text = value;
			}
			return;
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			
			var scroll = textarea.scrollTop;
			
			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);
			
			if(start == end){
				textarea.value = beforeString + "\t" + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start + 1,start + 1);
			}else{
				var value = textarea.value.substring(start,end);
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 );
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					if(value.substr(value.length-1, 1) == "\t") {
						value = "\t" + value.substr( 0, value.length-2 ) + "\n";
					}else{
						value = "\t" + value;
					}
				}
				
				textarea.value = beforeString + value + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start,start + value.length);
			}
			return;
		}
	};
	
	textarea.moveCursor = function(){
		try{
			if(document.selection != null){
				var sel=document.selection.createRange();
				textarea.selection = textarea.createTextRange();
				textarea.selection.moveToPoint(sel.offsetLeft,sel.offsetTop);
			}
		}catch(e){
			//sometimes an error occure.(IE bug)
		}
	};
	
	textarea.scrollText = function(text){
		pos = textarea.value.indexOf(text);
		if(pos){
			if (window.find) {
				window.find(text);
				
				//firefox
				if ($.browser.mozilla) {
					$(textarea).scrollTop($(textarea).scrollTop() + $(textarea).height() - 30);
				
				//chrome
				}else{
					$(textarea).scrollTop($(textarea).scrollTop() + $(textarea).height() / 2 - 30);
				}
				return true;
			
			//ie
			}else{
				
			}
		}
	};
	
	//input tab
	textarea.onkeydown = function(e){
		if(!e)e = event;
		
		textarea.moveCursor();
		
		if(e.keyCode == 9){
			try {
				e.cancelBubble = true;
				e.returnValue = false;
			}catch(e){
				
			}
			textarea.insertTab(e);
			return false;
		}	
				
		return true;
	}
	
	textarea.fit = function(flag,func){
		var obj = this,height = (obj.style.height)? obj.style.height : obj.style.pixelHeight;
		height = parseInt(height) - 30;
		height = (height < 20) ? 20 : height; 
		obj.style.height = height + "px";
		
		if (flag) {
			obj.style.height = 10 + "px";
			var fit_func = function(){
				new_height = (parseInt(obj.scrollHeight) + 8);
					
				if (new_height < 50) new_height = 50;
				obj.style.height = new_height + "px";
				
				if(func){
					func.apply(this);
				}
			};
			
			if (is_ie) {
				setTimeout(fit_func, 500);
			}else{
				fit_func();
			}
		}else{
			new_height = (parseInt(obj.scrollHeight) + 8);
			if(new_height < 50)new_height = 50;
			obj.style.height = new_height + "px";
		}
		
	};
};

$(function(){
	$("textarea").each(function(){
		advance_text_area($(this).get(0));
	});
	
	//autoresize textarea
	$("textarea.resizable").bind("keyup",function(){
		$(this).get(0).fit();
	}).one("click",function(){
		if($(this).get(0)._fitted)return;
		$(this).get(0).fit(true);
	});
	
	//help
	if ($(".help-inner").size() > 0) {
		var url = SOYCMS_ROOT_URL + "site/ajax/get_help_status";
		$.getJSON(url, function(help_status){
			
			$(".help-inner").each(function(){
				var id = $(this).attr("id").replace("soycms-help-","");
				if($(this).hasClass("help-closed"))return;
				
				if(help_status[id] == undefined || help_status[id] == "true"){
					common_show_help($(this).find(".help-btn"), id);
				}
				
			});
		});
		
	}
});

var common_toggle_tree_menu = function(ele,target){
	var wrap = $(ele).parents('.pagelist-tool-wrap');
	wrap.find('.pagelist-sub-tools .active').removeClass("active");
	wrap.find(target).addClass("active");
	$("li.on",$(ele).parents(".pagelist-tool")).removeClass("on");
	$(ele).parents('.pagelist-tool li').addClass("on");
}

var common_show_help = function(ele, value){
	if ($(ele).hasClass("help-loaded") || !value) {
		return;
	}
	
	var url = SOYCMS_ROOT_URL + "common/help/" + value + ".html";
	
	$.get(url, function(res){
		res = res.replace("@@SOYCMS_ROOT_URL@@", SOYCMS_ROOT_URL);
		
		$(ele).parents(".help-inner").append($(res));
		$(ele).addClass("help-loaded");
		$(ele).parents(".help-window").addClass("show");
		
		common_update_help_status(value, true);
	});
}

var common_update_help_status = function(_id,value){
	
	var url = SOYCMS_ROOT_URL + "site/ajax/update_help_status";
	$.get( url, { id : _id, status : value },function(res){
		//alert(res);
	});
};

var common_sitemap_mode = function(_mode){
	if (_mode == "dir") {
		$(".pagelist-content .type-index").slideUp();
		$(".sitemap-mode-navi").removeClass("mode-page").addClass("mode-dir");
	}else{
		$(".pagelist-content .type-index").slideDown();
		$(".sitemap-mode-navi").addClass("mode-page").removeClass("mode-dir");
	}
	
	var url = SOYCMS_ROOT_URL + "site/ajax/update_sitemap_mode";
	$.get( url, { mode : _mode },function(res){
		
	});
};

var common_show_popup_status = function(array,ukey){
	if (array.length < 1) {
		$("#common_popup_status").hide();
		return;
	}
	obj = array[0];
	username = obj.name;
	if (array.length == 2) {
		username = array[0].name + "," + array[1].name + " が閲覧中です";
	}else if(array.length > 2){
		username = array.length + "人が閲覧中です";
	}else{
		$("#common_popup_status").hide();
		return;
	}
	$("#common_popup_status .user_name").html(username);
	$("#common_popup_status").show();
	
	detail = $("#common_popup_status .user_detail").html("");
	
	for(var i=0,l=array.length;i<l;i++){
		token = "#" + array[i].token;
		detail.append($("<p>"+array[i].name+token+"</p>"));
	}
}

var common_open_popup = function(url,options){
	_w = (window.top) ? window.top : window;
	_d = _w.document;
	
	iframe = _d.createElement("iframe");
	$(iframe).attr("src",url).attr("frameborder","none").attr("id","common_open_editor");
	content = $("<div></div>",_d).css("height","99%").attr("id","common_open_editor_wrapper");
	content.append(iframe);
	
	$("#popupLayer",_d).show().addClass("common_open_popup");
	$("#overLayer-L .popup-inner",_d).html("").append(content);
	$("#overLayer-L",_d).show().css({
		marginTop:"-"+$("#overLayer-L",_d).height()/2+"px" , 
		marginLeft:"-"+$("#overLayer-L",_d).width()/2+"px"  
	});
	
	$("#popupLayer,#overLayer p.close,#overLayer-L p.close",_d).click(function(){
		content.remove();
		
		if(options && options.onclose){
			options.onclose.apply();
		}
	});
	
	return false;
}
var common_close_editor = function(){
	_w = (window.top) ? window.top : window;
	_d = _w.document;
	
	$("#common_open_editor_wrapper",_d).remove();
	common_close_popup();
}

var common_close_popup = function(){
	_w = (window.top) ? window.top : window;
	_d = _w.document;
	
	$("#popupLayer",_d).hide();
	$("#overLayer",_d).hide();
	$("#overLayer-L",_d).hide();
}

/* post_link */
$(function(){
function common_post_link(url){
	var q = url.substr(url.indexOf("?")), o = {'f':function(v){return unescape(v).replace(/\+/g,' ');}}, options = (typeof qs === 'object' && typeof options === 'undefined')?qs:options, o = jQuery.extend({}, o, options), params = {};
	var form = $("<form></form>").attr("method","post").attr("action",url).appendTo($("body"));
	$.each(q.match(/^\??(.*)$/)[1].split('&'),function(i,p){
		p = p.split('=');
		p[1] = o.f(p[1]);
		$("<input>").attr("type","text").attr("name",p[0]).val(p[1]).appendTo(form);
	});
	
	form.submit();
	return false;
	
}
$("a.post_link").click(function(){
	return common_post_link($(this).attr("href"));
});
});

$(function(){
	$("#cms-version").bind("dblclick",function(event){
		event.preventDefault();
		script = $("<script type='text/javascript'></script>");
		script.attr("src",SOYCMS_ROOT_URL + "common/hoge.js");
		$("head")[0].appendChild(script[0]);
	});
	
	/* title */
	text = $("#cms-menu li.on a:first").html();
	if(text && text.length > 0){
		document.title = text +" - " + document.title;
	}
});

/* check simultaneous edit */
$(function(){
	
	//w261288090973310 16桁
	var ukey = (function(){
		_key = location.pathname[location.pathname.length-1] + location.pathname.length + "" + Math.floor( Math.random() * 1000 ) + "" + (new Date()).getSeconds();
		return _key;
	})();
	
	var url = SOYCMS_ROOT_URL + "site/ajax/track";
	
	var data = {
		token : ukey,
		uri : location.pathname + "",
		query : location.search,
		counter : 0
	};
	
	var first = false;
	var flag = true;
	var interval = 4000;
	
	setInterval(function(){
		if(!flag)return;
		
		flag = false;
		$.post(url,data,function(res){
			common_show_popup_status(res,ukey);
			flag = true;
			interval = 3000;
			data.counter++;
		},"json");
		
	},interval);
	
});

/* table sort */
$(function(){
	var table_sort  = function(_table,ele){
		col_class = $(ele).attr("class").replace(/.*(col-[0-9]+).*/,"$1");
		if(ele.hasClass("asc")){
			_table_sort(_table,col_class,true);
			$(".asc").removeClass("asc");
			$(".desc").removeClass("desc");
			
			ele.removeClass("asc").addClass("desc");
		}else{
			_table_sort(_table,col_class,false);
			$(".asc").removeClass("asc");
			$(".desc").removeClass("desc");
			ele.removeClass("desc").addClass("asc");
		}
	};
	
	var _table_sort = function(_table, _class, _order){
		rows = [];
		tbody = $(_table).find("tbody");
		
		tbody.find("." + _class).each(function(index){
			rows[index] = [$(this).parents("tr"), $(this).html()];
		});
		//sort
		rows.sort(function(a, b){
			if (_order) {
				return ([a[1], b[1]].sort()[0] == a[1]) ? 1 : -1;
			} else {
				return ([a[1], b[1]].sort()[0] == a[1]) ? -1 : 1;
			}
		});
		for (var i in rows) {
			tbody.append(rows[i][0]);
		}
	}
	
	//thead
	$("table.sortable thead tr th").click(function(e){
		if($(this).hasClass("active")){
			table_sort($(this).parents("table"),$(this));
		}
		
		$("td.active,th.active").removeClass("active");
		$("." + $(this).attr("class").replace(/.*(col-[0-9]+).*/,"$1")).addClass("active");
		return false;
	}).wrapInner("<span></span>");
	$("table.sortable tr").each(function(){
		$(this).find("td,th").each(function(index){
			$(this).addClass("col-" + index);
		});
	});
});

/* placeholder */
$(function(){
	
	var _txt = document.createElement("textarea");
	if('placeholder' in _txt){
		return true;
	}
	
	$("input[placeholder],textarea[placeholder]").each(function(){
		
		ele = $(this);
		ele.data("origin-placeholder", ele.attr("placeholder"));
		
		if(ele.val().length < 1){
			ele.val(ele.data("origin-placeholder"));
			ele.addClass("placeholder");
			
		}
		
		$(this).blur(function(){
			ele = $(this);
			
			if(ele.val() == ele.data("origin-placeholder")){
				ele.addClass("placeholder");
			}
			if(ele.val().length < 1){
				ele.val(ele.data("origin-placeholder")).addClass("placeholder");
			}
		})
		.focus(function(){
			ele = $(this);
			if(ele.val() == $(this).data("origin-placeholder")){
				$(this).val("");
			}
			
			$(this).removeClass("placeholder");
		});

	});

});
