/**
 * 新しい要素を追加
 * @param {Object} ele
 * @param {Object} _section
 * @param {Object} _snippet
 */
aobata_editor.append_new_section = function(ele,_section,_snippet,_form){
	
	var url = EDITOR_ACTION_URL;
	var section_list = $(ele).parents(".section_list");
	var replace = (_form) ? $.map($("input,textarea,select",_form),function(n,i){
		if($(n).attr("name").length < 1)return null;
		if(!$(n).val())return $(n).attr("name") + "=";
		return $(n).attr("name") + "=" + $(n).val().replace(/&/g,'%26');
	}).join("&") : null;
	var count = $(".section_list").size();
	
	aobata_editor.hideAllPopup();
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
aobata_editor.insert_new_section = function(ele,_section,_snippet,_form){
	
	aobata_editor.hideAllPopup();
	
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
		
		aobata_editor.hideAllPopup();
	});
	
}

/**
 * セクションのフォームを表示
 */
aobata_editor.show_new_section_form = function(id){
	
	aobata_editor.hideAllPopup();
	
	form = $("#" + id + "_section_form");
	
	form.find("input,textarea,select").val("");	//clear all values
	
	try {
		parentEle = form.parents().filter(".panel-parts");
	}catch(e){
		form.show();
		return;
	}
	
	form.show();
	
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
	
	form.show();
	return false;
}

/**
 * 挿入エリアの表示
 */
aobata_editor.show_insert_lines = function(ele){
	$(ele).parents(".article-header").append($("#append_new_section"));
	aobata_editor.hideAllPopup();	//隠す
	
	$("#append_new_section").slideToggle();
}



/**
 * open attachments
 */
aobata_editor.show_attachments = function(func){
	$("#show_attachment_btn").click();
	prepare_attachment(func);
};

/**
 * open insert popup
 */
aobata_editor.show_insert_popup = function(ele){
	aobata_editor.hideAllPopup();
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
};

aobata_editor.select_attachment = function(inner,link){
	aobata_editor.get().insertHTML('<span id=____new____img___></span>');
	d = aobata_editor.get().getWindow().document;
	if(inner.match(/https?:\/\//)){
		if(inner == link)link = null;
		inner=$('<img />').attr('src',inner);
	}else{
		inner=$('<span></span>').html(inner);
	}
	try{
		if(link && link != "image"){
			newimg=$('<a></a>');
			newimg.append(inner);
			newimg.attr('href',link);
			
		}else{
			newimg=inner;
		}
		
		$('#____new____img___',d).after(newimg).remove();
	
	//for ie6 bug
	}catch(e){
		aobata_editor.get().insertHTML(newimg.html());
	}
};
