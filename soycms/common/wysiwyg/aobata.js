/*
 * aobata_editor
 * @requre jquery.js
 */
var aobata_editor = function(target,options){
	this.initialize(target, options);
	aobata_editor.count++;
	aobata_editor.editors.push(this);
};
aobata_editor.editors = [];
aobata_editor.option = {
	base_url : "",
	format : true,
	plugins : ["common","tag_format","buttons"]
};
aobata_editor.count = 0;
aobata_editor.root = "";
aobata_editor.active = null;
aobata_editor.mouseX = 0;
aobata_editor.mouseY = 0;
aobata_editor.is_IE = /*@cc_on!@*/false;
aobata_editor.isWebkit = navigator.userAgent.indexOf('WebKit/') > -1;
aobata_editor.changed = false;
aobata_editor.paramView = null;
aobata_editor.tableContextMenu = null;
aobata_editor.debug = false;
aobata_editor.ukey = "";

aobata_editor.get = function(val){
	if (!val) {
		if (aobata_editor.active) {
			aobata_editor.active.onReturnFocus();
			return aobata_editor.active;
		} else {
			aobata_editor.editors[0].onReturnFocus();
			return aobata_editor.editors[0];
		}
	}
	if ($(val).size() < 1) throw "error";
	return $(val)[0].editor;
}

aobata_editor.find = function(ele){
	var textarea = $(ele).parents(".section_list").find(".aoboata_editor_textarea");
	if(textarea.size() > 0){
		return textarea[0].editor;
	}
	
	throw "error";
}

aobata_editor.syncAll = function(){
	for(var i=0,l=aobata_editor.editors.length;i<l;i++){
		aobata_editor.editors[i].sync();
	}
};
aobata_editor.expandAll = function(flag){
	if(!flag)flag = false;
	for(var i=0,l=aobata_editor.editors.length;i<l;i++){
		aobata_editor.editors[i].adjustSize(flag);
	}
};
aobata_editor.collapseAll = function(flag){
	if(!flag)flag = false;
	for(var i=0,l=aobata_editor.editors.length;i<l;i++){
		aobata_editor.editors[i].collapseSize(flag);
	}
};
aobata_editor.close = function(){
	aobata_editor.get().header.hide();
	aobata_editor.get().footer.hide();
}

aobata_editor.table_helper = {
	
	cell : null
	,row : null
	,col : null
	
	,_prepare : function(){
		if (!aobata_editor.table_helper.cell) {
			aobata_editor.table_helper._finish();
			return false;
		}
		
		
		aobata_editor.table_helper.row = 
			$(aobata_editor.table_helper.cell.parents("tr").get(0));
		
		col = $([]);
		_index = 0;
		$.map($(aobata_editor.table_helper.cell).prevAll(),function(n,i){
			_index += ($(n).attr("colspan")) ? $(n).attr("colspan") : 1
		});
		$(aobata_editor.table_helper.cell.parents("table").get(0)).find("tr").each(function(i,ele){
			_ele = $(ele).find("td:eq("+_index+")");
			if (_ele.size() > 0) {
				col.push(_ele.get(0));
			}
		});
		
		aobata_editor.table_helper.col = col;
		
		return true;
	}
	
	,_finish : function(){
		aobata_editor.tableContextMenu.hide();
	}
	
	,prepend : function(type){
		if(!aobata_editor.table_helper._prepare())return;
		
			if(type == "row"){
				var new_row = aobata_editor.table_helper.row.clone();
				new_row.find("td").html("&nbsp;");
				aobata_editor.table_helper.row.before(new_row);
			}else{
				var new_col = aobata_editor.table_helper.col.clone();
				aobata_editor.table_helper.col.before($("<td>&nbsp;</td>"));
			}
		
		
		aobata_editor.table_helper._finish();
	}
	
	,append : function(type){
		if(!aobata_editor.table_helper._prepare())return;
		
			if(type == "row"){
				var new_row = aobata_editor.table_helper.row.clone();
				new_row.find("td").html("&nbsp;");
				aobata_editor.table_helper.row.after(new_row);
			}else{
				aobata_editor.table_helper.col.after($("<td>&nbsp;</td>"));
			}
		
		aobata_editor.table_helper._finish();
	}
	
	,remove : function(type){
		if(!aobata_editor.table_helper._prepare())return;
		
		if(type == "row"){
			aobata_editor.table_helper.row.remove();
		}else if(type == "col"){
			aobata_editor.table_helper.col.remove();
		}else{
			aobata_editor.table_helper.cell.remove();
		}
		
		aobata_editor.table_helper._finish();
	}
	
};

//
aobata_editor.plugin = {
	
	_plugins : {},
	
	register : function(id, func){
		if("undefined" != typeof(this._plugins[id]))return;
		this._plugins[id] = func;
	},
	
	load : function(editor){
		for(var i in this._plugins){
			this._plugins[i].apply(this, [editor]);
		}
	}

};
aobata_editor.plugin.eventHandler = function(type,editor){
	this.initialize(type,editor);
}
aobata_editor.plugin.eventHandler.prototype = {
	_type : null,
	_editor : null,
	_events : [],
	argument : null,
	
	initialize : function(type,editor){
		this._type = type;
		this._editor = editor;
		this._events = [];
	},
	
	add : function(_func){
		this._events.push(_func);
	},
	
	dispatchEvent : function(e){
		_editor = this._editor;
		var res = false;
		
		this.argument = e;
		
		for(var i in this._events){
			res = this._events[i].apply(this,[_editor,this.argument]);
		}
		
		return res;
	}
};

//prepare 
aobata_editor.prepare = function(){
	
	$("script").each(function(){
		if($(this).attr("src") && $(this).attr("src").match(/aobata.js/)){
			aobata_editor.root = $(this).attr("src").replace(/\/aobata.js.*/,"/");
			return false;
		}
	});
	
	var link = document.createElement("link");
	link.setAttribute("rel","stylesheet");
	link.setAttribute("href",aobata_editor.root+'dat/style.css');
	document.getElementsByTagName("head")[0].appendChild(link);
	
	var script = $('<script type="text/javascript" src="'+aobata_editor.root+'dat/range.js"></script>');
	$($("head")[0]).append(script);
	
	var script = $('<script type="text/javascript" src="'+aobata_editor.root+'dat/panel.js"></script>');
	$($("head")[0]).append(script);
	
	//load plugins
	for(var i in aobata_editor.option.plugins){
		var script = $('<script type="text/javascript" src="'+aobata_editor.root+'plugins/'+aobata_editor.option.plugins[i]+'.js'+"?t="+(new Date()).getTime()+'"></script>');
		$($("head")[0]).append(script);
	}
	
	//generate ukey
	aobata_editor.ukey = "";
};


aobata_editor.prototype = {
	
	wrapper : null,
	frame : null,
	tool :null,
	footer : null,
	textarea : null,
	options : null,
	paramView : null,
	active : false,
	carent : null,
	mode : true, // true is mode of wysiwyg.
	command : [], 
	
	
	initialize : function(target, options){
		
		if(!target)return;
	
		var inst = this;
		var isWebKit = navigator.userAgent.indexOf('AppleWebKit/') > -1;
		
		this.options = {
			editable : true,
			locked : false
		};
		
		this.command = [];
		
		this.textarea = $(target);
		$.extend(this.options,options)
		this.textarea[0].editor = this;
		this.textarea.addClass("aoboata_editor_textarea");
		
		//set id
		if(this.textarea.attr("id").length<1)this.textarea.attr("id", "aobata_editor_textarea_" + aobata_editor.count);
		
		//wrap div
		this.textarea.wrap("<div id='"+this.textarea.attr("id")+"_editor_wrap' class='aobata_editor_wrap'></div>");
		
		//init iframe
		this.frame = $("<iframe id='"+this.textarea.attr("id")+"_editr_fr' class='aobata_editor_fr' src='"+aobata_editor.root+"dat/blank.html?t="+(new Date())+"' frameborder='0'></iframe>");
		this.textarea.after(this.frame);
		
		this.wrapper = this.frame.parents(".section_list");
		this.footer = this.wrapper.find(".article-footer");
		this.header = this.wrapper.find(".article-header");
		
		
		//hide textarea
		this.textarea.hide().bind("focus",function(){
			inst.header.show().addClass("text-mode");
			inst.footer.hide();
		});
		
		
		//bulid param view
		if(!aobata_editor.paramView){
			aobata_editor.paramView = $("<div id='aobata_editor_param_view' class='aobata_editor_param_view'></div>");
			$(document.body).append(aobata_editor.paramView);
			aobata_editor.paramView.append($("<div class='panel-layers'></div>"));
			
			var text_param_html = (aobata_editor.is_IE)
										? aobata_editor.root + "dat/param_text_ie.html"
										: aobata_editor.root + "dat/param_text.html";
			
			$(".panel-layers",aobata_editor.paramView).append(
				$("<div class='type_img'></div>").load(aobata_editor.root + "dat/param_img.html",function(){
					$(this).find('input.input-link')
					.focus(function() { $(this).addClass("active") })
					.blur(function() { if ($(this)[0].value == '') { $(this).removeClass("active") } });
				})
			).append(
				$("<div class='type_text'></div>").load(text_param_html,function(){
					$(this).find('input.input-link')
					.focus(function() { $(this).addClass("active") })
					.blur(function() { if ($(this)[0].value == '') { $(this).removeClass("active") } });
				})
			);
			
			aobata_editor.paramView.css("zIndex","200000");	//for chrome bug
			
			//table param-view
			aobata_editor.tableContextMenu = $("<div id='aobata_editor_table_menu'></div>");
			aobata_editor.tableContextMenu.hide();
			$(document.body).append(aobata_editor.tableContextMenu);
			aobata_editor.tableContextMenu.load(aobata_editor.root + "dat/param_table.html");
		}
		
		//copy properties
		this.paramView = aobata_editor.paramView;
		this.tableContextMenu = aobata_editor.tableContextMenu;
		
		//build tool
		if (this.options.editable) {
			this.tool = $("<div id='" + this.textarea.attr("id") + "_editor_param_tool' class='aobata_editor_param_tool'></div>");
			this.header.find(".wysiwyg-mode-panel").append(this.tool);
			
			this.tool.load(aobata_editor.root + "dat/tool.html");
			this.tool.bind("click",function(){inst.closeParam();});
		}
		
		//build footer
		if(this.footer){
			this.footer.bind("click",function(){
				_hideallpopup();
				inst.closeParam();
			});
		}
		
		
		var frame = this.frame[0];
		
		try{
			_onload = function(t){
				
				t_window = t.getWindow();
				t_window.editor = t;
				d = t_window.document;
				
				if(!d.body){
					return;
				}
				
				if(aobata_editor.option.custom_css){
					var head = d.getElementsByTagName("head")[0];
					$(aobata_editor.option.custom_css.split(",")).map(function(key, css){
						var link = d.createElement("link");
						link.setAttribute("rel", "stylesheet");
						link.setAttribute("href", css);
						head.appendChild(link);
					});
				}
				
				try {
					//innerHTML
					t.setHTML(t.textarea.val());
				}catch(e){
					alert("failed to setHTML");
				}
				
				if(t.options.locked){
					aobata_editor.expandAll(true);
					return;
				}
				
				//textarea focus event
				$(t.textarea).bind("focus",function(){
					t.isActive(true);
				});
				
				if (t.options.editable) {
					
					//save cursor pos
					$(d).bind("click", function(event){
						t.saveCaret();
						t.isActive(true);
						
						if(t.getWindow().document.body.innerHTML.match(/^<p><\/p>\n*$/gi)){	//初期状態
							event.preventDefault();
							
							var range = t.getRange();
							ele = $("p",t.getWindow().document.body).html("<span id='__caret'>__blank__</span>" + "\u200B");
							if (range.contents) {
								range.contents(ele[0]);
							}else{
								range.sel(ele[0].firstChild);
							}
							t.removeCaret();
							
							
							return false;
						}
						
					}).bind("mousedown",function(e){
						
						if (aobata_editor.is_IE) {
							var offset = t.frame.offset();
							aobata_editor.mouseY = offset.top + e.clientY - $(t.getWindow()).scrollTop();
						}else{
							var offset = t.frame.offset();
							aobata_editor.mouseY = offset.top + e.pageY - $(t.getWindow()).scrollTop();
						}
						
					}).bind("mouseup", function(e){
						
						if (aobata_editor.is_IE) {
							var offset = t.frame.offset();
							tmp = offset.top + e.clientY - $(t_window).scrollTop();
							if(tmp >= aobata_editor.mouseY)aobata_editor.mouseY = tmp;
						}else{
							var offset = t.frame.offset();
							tmp = offset.top + e.pageY - $(t_window).scrollTop();
							if(tmp >= aobata_editor.mouseY)aobata_editor.mouseY = tmp;
						}
						try {
							var range = t.getRange();
							var ele = (e.target && e.target.tagName.toLowerCase() == "img") ? e.target : (e.target) ? e.target : t.getRange().end()[0];
							t.updateParamPos();
							
							
							if (t.lastRange) {
								start = range.start();
								if (t.lastRange[0][0] == start[0] &&
								t.lastRange[0][1] == start[1]) {
									range._collapsed = true;
								}
							}
							
							//when cursor is out of iframe 
							if (ele.tagName.match(/html/i)) {
								ele = $("body", ele)[0];
							}
							
							t.focusElement(ele, "click");
							
							t.lastRange = [range.start(), range.end()];
						}catch(e){
							//alert("failed in mouseup " + e);
						}
						
						t.header.show();
						t.footer.show();
					
						
					}).bind("mousemove", function(e){
						
						
					}).bind("keyup", function(event){
						t.keyup(event);
						
						
					}).bind("keydown", function(event){
						t._last_keycode_result = null;
						t.keydown(event);
						aobata_editor.changed = true;
						t.saveCaret();
						
						range = t.getRange();
						ele = range.end()[0];
						t._currentfocuselement = ele;
						
						//save the last keycode
						if(t._last_keycode_result){
							t._last_keycode = t._last_keycode_result
							return;
						}
						t._last_keycode = event.keyCode;
						
					}).bind("contextmenu",function(e){
						
						var range = t.getRange();
						var ele = (e.target && e.target.tagName.toLowerCase() == "img") ? e.target : (e.target) ? e.target : t.getRange().end()[0];
						var offset = t.frame.offset();
						if(ele.tagName.match(/td/i)){
							t.execCommand("tableContext",ele,{
								x : e.pageX + offset.left,
								y : e.pageY + offset.top
							});
							return false;
						}
						
					});
					
					if(aobata_editor.isWebkit){
						t_window.addEventListener("paste",function(event){
							if(event.clipboardData){
								text = event.clipboardData.getData("text/html");
								if(!text)text = event.clipboardData.getData("Text");
								//replace
								text = text.replace(/<meta .*?>/g,"")
									.replace(/\s*style=".*?"/g,"")
									.replace(/<[^>]*><\/[^>]*>/g,"");
								
								
								
								//paste
								t.insertHTML(text);
								event.preventDefault();
							}
						},false);
					}
					
				}else{
					
					/*
					 * no wisywig
					 */
					
					//set overlay
					t.wrapper.prepend("<div class='aobata_preview_overlay'></div>").css({
						position:"relative"
					}).bind("click",function(){
						t.isActive(true);
						$("#insert_new_sections").hide();		//fix bug
						t.header.show().addClass("text-mode");
						t.footer.show();
					});
					
					$(d).bind("click",function(){ 
						t.isActive(true);
						$("#insert_new_sections").hide();		//fix bug
						t.header.show().addClass("text-mode");
						t.footer.show();
					});
				}
				
				_downallow();
				
				if (t.options.editable) {
					//on wysiwyg
					if(aobata_editor.is_IE){
						d.body.contentEditable = true;
					}else{
						d.designMode = "on";
					}
				}
				
				aobata_editor.expandAll(true);
				
				//set event dispatcher
				$.map([
					"beforeGetHTML",
					"onGetHTML",
					"onInit",
					"onSetHTML"
				],function(type){
					t[type] = new aobata_editor.plugin.eventHandler(type,t);
				});
				
				aobata_editor.plugin.load(t);
				
				
			};// onload function
				
			
			if(document.all){
				frame.editor = this;
				frame.onreadystatechange = function(){
					if(this.readyState == "complete"){
						frame.onreadystatechange = null;
						_onload(frame.editor);
					}				
				}
			}else{
				frame.onload = function(){
					_onload(inst);
				}
			}
			
		}catch(e){
			alert("onload:" + e);
		}
		
		$(this.textarea).parents("form").bind("submit",function(){
			inst.sync();
			aobata_editor.changed = false;
		});
		
	},
	
	showParam : function(){
		if(this.paramView)this.paramView.show().find(".panel-layers").show();;
		if(this.footer)this.footer.show();
	},
	
	closeParam : function(){
		if(this.paramView)this.paramView.hide();
		if(this.footer)this.footer.hide();
	},
	
	updateParamPos : function(){
		
		/*
		 * ie(before ie8) can't recognize "position:fixed!"
		 */
		if (aobata_editor.is_IE && !document.querySelectorAll) {
			
			var diff = 18;
			new_top = aobata_editor.mouseY + diff;
			
			aobata_editor.paramView.css({
				position : "absolute",
				width : "550px",
				top: new_top,
				left: this.frame.offset().left + 10
			});
		}
	},
	
	focusElement : function(_ele, event){
		
		t = this;
		d = t.getWindow().document;
		var showParam = false;
		
		var range = this.getRange();
		ele = $(_ele);
		
		while(ele[0].nodeType == 3 /* Node.TEXT_NODE */){ //TEXT NODE
			ele = $(ele).parent();
		}
		
		var tagName = $(ele)[0].tagName.toLowerCase();
		var tagType = "other";
		
		//failed document or ...
		if(!$(ele)[0].tagName){
			alert("focus element error");
			return;
		}
		
		//invalid element
		if(tagName.match(/html/)){
			return;
		}
		
		range = this.getRange();
		
		//select by TagName
		switch(tagName){
			case "img":
				tagType = "img";
				showParam = true;
				break;
			case "a":
				showParam = true;
				this.getRange().sel(ele);
				break;
			case "span":
			case "b":
			case "strong":
			case "s":
			case "del":
				if (event == "click") {
					showParam = true;
					range = this.getRange();
					if (range.collapsed) {
						range.sel(ele);
					}
				}
				break;
			case "h1":
			case "h2":
			case "h3":
			case "h4":
			case "h5":
			case "h6":
				if (event == "click") {
					showParam = true;
				}
				break;
			default:
				if($(ele).parents("a").size() > 0){
					showParam = true;
					break;
				}
				showParam = (range._collapsed != undefined) ? !range._collapsed : !range.collapsed;
				break;
		}
		
		t.updateParam(ele);
		
		if (showParam) {
			$(".downpanel-layer,.downmenu-layer,",t.paramView).hide();
			$(".panel-layers").hide();
			t.showParam();
		}else{
			t.closeParam();
		}
		t.paramView.attr("class",function(i,c){
			return c.replace(/tag-.+/g, '');
		});
		t.paramView.addClass("tag-" + tagType);
		
		$(".aobata_current_edit_element",d).removeClass("aobata_current_edit_element");
		
		if (!tagName.match(/html|body/)) {
			ele.addClass("aobata_current_edit_element");
			if(ele.parents("body").size() < 1){
				$(t.getWindow().body).append(ele);
			}
		}
		t._currentfocuselement = ele[0];
	},
	
	updateTool : function(ele){
		
	},
	
	updateParam : function(ele){
		t = this;
		
		$(".property-panel a.active").removeClass("active");
		
		//attributes(text)
		$("#attr_src").val($(ele).attr("src") + "");
		
		link = ($(ele).attr("href")) ? $(ele).attr("href")
		 	: ($(ele).parents("a").size() > 0) ? $(ele).parents("a").attr("href") : "";
			
		if(link === null)link = "";
		
		$("#attr_href").val(link);
		$("#attr_target").val($(ele).attr("target") + "");
		$("#attr_title").val($(ele).attr("title") + "");
		$("#attr_id").val($(ele).attr("id") + "");
		$("#attr_class").val($(ele).attr("class").replace(/aobata_[^\s]+\s?/g,"") + "");
		
		try {
			if (link.length > 0) {
				$("#btn-link").parent().hide();
				$("#btn-mail").parent().hide();
				$("#link_input_wrap").show();
				$("#attr_href").addClass("active");
			} else {
				$("#btn-link").parent().show();
				$("#btn-mail").parent().hide();
				$("#link_input_wrap").hide();
				$("#attr_href").removeClass("active");
			}
		}catch(e){
			
		}
		
		//link
		if(link.match(/^http/)){
			$(".property-panel a.btn-link").addClass("active");
		}
		if(link.match(/^mailto/)){
			$(".property-panel a.btn-mail").addClass("active");
		}
		
		//bold
		bold = $(ele).css("fontWeight");
		if(bold == "bold" || (!isNaN(bold) && bold > 400)){
			$(".property-panel a.btn-bold").addClass("active");	
		}
		style = $(ele).css("fontStyle");
		if(style == "italic"){
			$(".property-panel a.btn-italic").addClass("active");
		}
		style = $(ele).css("textDecoration");
		if(style == "underline"){
			$(".property-panel a.btn-underline").addClass("active");
		}
		if(style == "line-through"){
			$(".property-panel a.btn-del").addClass("active");
		}
		
		//color
		color = $(ele).css("color");
		$(".property-panel a.btn-fontcolor").css("backgroundColor",color);
		color = $(ele).css("backgroundColor");
		$(".property-panel a.btn-bgcolor").css("backgroundColor",color);
		
		//formatblock
		tagName = null;
		if ($(ele).parents("h1,h2,h3,h4,h5,h6").size() > 0) {
			tagName = $(ele).parents("h1,h2,h3,h4,h5,h6").get(0).tagName.toLowerCase();
		} else {
			tagName = $(ele).get(0).tagName.toLowerCase();
		}
		if (tagName.match(/(p|h[0-9])/i)) {
			$("#format_select").val(tagName);
		}else{
			$("#format_select").val("p");
		}
		
		//attributes(img)
		src = t.convertUrlByBase($(ele).attr("src"),true);
		$("#img_attr_src").val(src + "");
		$("#img_attr_alt").val($(ele).attr("alt"));
		$("#img_attr_href").val( ($(ele).parents("a").attr("href")) ? $(ele).parents("a").attr("href") : "" );
		$("#img_attr_target").val( ($(ele).parents("a").attr("target")) ? $(ele).parents("a").attr("target") : "" );
		$("#img_attr_width").val(($(ele)[0].getAttribute("width")) ? $(ele).attr("width") + "" : "");
		$("#img_attr_height").val(($(ele)[0].getAttribute("height")) ? $(ele).attr("height") + "" : "");
		$("#img_attr_id").val($(ele).attr("id") + "");
		$("#img_attr_class").val($(ele).attr("class").replace(/aobata_[^\s]+\s?/g,"") + "");	
		
		if ($("#img_attr_src").val() != "undefined" && $("#img_attr_src").val().length > 0) {
			$("#img_attr_src").addClass("active");
			if($("#img_preview").size() < 1){
				$(".panel-image-preview").append($("<img />").attr("id","img_preview"));
			}
			$("#img_preview").attr("src",$("#img_attr_src").val()).show();
		}else{
			$("#img_preview").hide();
		}
		if($("#img_attr_href").val().length>0)$("#img_attr_href").addClass("active");
		
		//align
		align = $(ele).css("verticalAlign");
		if(!align)align = "baseline";
		$("#img_align").val(align);
		
		//float
		float = $(ele).css("float");
		if(!float)float = "none";
		$("#img_float").val(float);
	},
	
	sync : function(){
		if (this.mode) {
			this.textarea.val(this.getHTML());
		}else{
			this.setHTML(this.textarea.val());
		}
	},
	
	setHTML : function(html){
		
		if(html.length < 1 || html.match(/^\n*$/) || html.match(/^<br[^>]*>\n*$/)){
			html = "<p></p>";
		}
		
		var t = this;
		var d = this.getWindow().document;
		var tmphtml = d.createDocumentFragment();
		var bodyfragment = d.createDocumentFragment();
		
		//do nothing
		if(aobata_editor.is_IE || !aobata_editor.option.format){
			d.body.innerHTML = html;
			return;
		}
		
		//createcontextualFragment
		var range = this.getRange();
		range.sel(d.body); //fix bug for webkit
		node = range.createContextualFragment(html);
		
		//build temporary fragment
		tmphtml.appendChild(node);
		var childNodes = tmphtml.childNodes;
		
		for(var i=0,l=childNodes.length;i<l;i++){
			bodyfragment.appendChild(childNodes[i].cloneNode(true));
		}
		
		if(!this.getWindow().document.body)return;
		
		this.getWindow().document.body.innerHTML = "";
		this.getWindow().document.body.appendChild(bodyfragment);
		
		/* convert relative url to absolute url in img */
		/* for firefox and others... */
		if (aobata_editor.option.base_url.length > 0) {
			$("img", d).each(function(){
				src = $(this).attr("src");
				
				$(this).attr(
					"src", 
					t.convertUrlByBase(src)
				);
			});
		}
	},
	
	getHTML : function(){
		d = this.getWindow().document;
		t = this;
		
		//fix bug(caret element is exists)
		while($("#__caret",d).size() > 0){
			$("#__caret",d).remove();
		}
		
		/* convert relative url to absolute url in img */
		$("img",d).each(function(){
			var img = new Image;
			img.src = $(this).attr("src");
			src = img.src;
			src = t.convertUrlByBase(src, true);
			$(this).attr("src",src);
		});
		
			
		try {
			this.beforeGetHTML.dispatchEvent(this.getWindow().document.body);
		}catch(e){
			
		}
		
		var html = $(this.getWindow().document.body).html();
		
		try {
			res = this.onGetHTML.dispatchEvent(html);
			if(res){
				html = res;
			}
		}catch(e){
			
		}
		
		/* reconvert absolute url to relative url in img */
		$("img",d).each(function(){
			var img = new Image;
			img.src = $(this).attr("src");
			src = img.src;
			src = t.convertUrlByBase(src);
			$(this).attr("src",src);
		});
		
		return html;
	},
	
	format : function(options){
		
		html = this.textarea.val();
		
		//execute textarea mode only
		if(this.mode){
			return;
		}
		
		
		for(var i in options){
			var option = options[i];
			var res = this.execCommand(option,html);
			if(res){
				html = res;
			}
		}
		
		this.textarea.val(html);
		return;
	},
	
	getWindow : function(){
		return this.frame[0].contentWindow;
	},
	
	getDocument : function(){
		return this.frame[0].contentWindow.document;
	},
	
	isActive : function(param, isStopEvent){
	
		t = this;
		d = window.document;
	
		if(window.parent){
			d = window.parent.document;
		}
		
		
		//hide all section's footer
		$(".article-footer",d).hide();
		$(".article-header",d).hide();
		
		_hideallpopup();
		_downallow();
		$("#append_new_section").hide();
		
		//hide table context menu
		t.tableContextMenu.hide();
		
		//remove classes
		$(".aobata_editor_actived",d).removeClass("aobata_editor_actived");
		$(".aobata_editor_appended",d).removeClass("aobata_editor_appended");
		
		if(param){
			this.wrapper.addClass("aobata_editor_actived");
			this.header.show();
			this.footer.show();
			
			$("#insert_new_sections").show().insertBefore(
				this.header.find(".wysiwyg-mode-panel .panel-line .panel-parts:last")
			);
			
			
			
		}else{
			this.wrapper.removeClass("aobata_editor_actived");
		}
		
		this.active = param;
		
		aobata_editor.active = this;
		
		this.adjustSize(true);
	},
	
	
	//keydown counter
	_keydown_tmp_counter : 0,
	
	keydown : function(event){
		var range = this.getRange();
		
		/* show param by keyboard */
		
		//press shift and move cursor
		if(event.keyCode >= 37 && event.keyCode <= 40 && event.shiftKey){
			if (event.keyCode % 2 == 0) {	//up and down
				this._keydown_tmp_counter += 2;
			}else{
				this._keydown_tmp_counter += 1;
			}
			if(event.metaKey){
				this._keydown_tmp_counter += 3;
			}
			if(this._keydown_tmp_counter > 3){
				this._keydown_tmp_counter = 0;	//reset
				this.focusElement(this.getRange().end()[0],true);
				this.showParam();
				return false;	
			}
			return true;
		}
		
		/* end of here */
		
		this._keydown_tmp_counter++;	//count key pressing
								//when param window is visible, counter must be 0;
		
		var _start = range.start(), _end = range.end();
		start = ele = _start[0];
		end = _end[0];
		
		_startText = (start) ? (start.nodeValue) ? start.nodeValue : start.innerHTML : "";
		_endText = (end) ? (end.nodeValue) ? end.nodeValue : end.innerHTML : "";
		
		//backspace
		if(event.keyCode == 8){
			this.closeParam();
			this._keydown_tmp_counter = 0;	//reset counter
			
			//for fix ie bug
			if(aobata_editor.is_IE && start.tagName && start.tagName.match(/img/i)){
				event.preventDefault();
				$(start).remove();
				return false;
			}
		}
		if(event.keyCode == 8 && this.getDocument().body.innerHTML.length < 1){
			this.insertHTML("<p>\u200B</p>");
			this._keydown_tmp_counter = 0;	//reset counter
			return false;
		}/* event backspace */
		
		//ctrl + a
		if((event.ctrlKey && event.keyCode == 65) || (event.metaKey && event.keyCode == 65)){
			this._currentfocuselement = this.getWindow().document.body;
			this.showParam();
			return true;
		}
		
		//enter
		if(event.keyCode == 13){
			
			if(!ele){
				this._keydown_tmp_counter = 0;	//reset counter
				return false;
			}
			
			this.closeParam();
			
			while(
				ele.nodeType == 3 /* Node.TEXT_NODE */
				|| (ele.tagName && ele.tagName.match(/span/i))
			){
				//when select body > #text 
				if(ele.parentNode.tagName && ele.parentNode.tagName.match(/body/i)){
					_keydown_tmp_counter = 0;	//reset counter
					this.wrapTextNode(ele);
				}
					
				ele = ele.parentNode;
			}
			
			//enter in paragraph
			if(ele.tagName.match(/(p|div|pre|code|body)/i)){
				
				event.preventDefault();
				event.stopPropagation();
				
				if (ele.tagName.match(/body/i)) {
					this.insertNewParagprah(ele);
					this._keydown_tmp_counter = 0;	//reset counter
					
					return false;
				}
				
				if(parseInt($(ele).css("marginLeft")) > 0){
					
					if(event.shiftKey){
						this.insertBreakLine();
						return false;
					}
					
					_ele = this.splitToParagraph(ele);
					_ele.css("marginLeft",$(ele).css("marginLeft"));
					this._currentfocuselement = _ele;
					this._last_keycode_result = 9;
					return false;
				}
				
				//pタグ 末尾
				//shift + enterで次の段落
				if (ele.tagName.match(/p/i) && event.shiftKey && _start[1] == _startText.length) {
					this.insertNewParagprah(ele);
					this._keydown_tmp_counter = 0;	//reset counter
					
					return false;
				}
				
				try {
					//現在の位置に<br />を挿入
					this.insertBreakLine();
					this._keydown_tmp_counter = 0; //reset counter
					return false;
				}catch(e){
					return true;
				}
			}
			
			//enter in heading
			if(ele.tagName.match(/(h[0-6])/i)) {

				
				//タグの途中
				if(_endText.length > 0 && _start[1] <= _startText.length){
					this._keydown_tmp_counter = 0;	//reset counter
					
					if (event.shiftKey) {
						event.preventDefault();
						event.stopPropagation();
						
						//現在の位置に<br>を挿入
						this.insertHTML("<br />");
						return false;
					}
					
					event.preventDefault();
					event.stopPropagation();
					
					
					//split html and create new paragprah.	
					this.splitToParagraph(ele);
					
					return false;
				
				//タグの末尾
				}else{
					event.preventDefault();
					event.stopPropagation();
					
					//ノードの後ろに追加
					this.insertNewParagprah(ele);
						
					this._keydown_tmp_counter = 0;	//reset counter
					return false;
				}
			}
			
		} /* event enter */
		
		//tab 
		if(event.keyCode == 9){
			
			event.preventDefault();
			
			//範囲選択の場合
			if (!range.collapsed || this._last_keycode == 9 || event.shiftKey) {
				if (event.shiftKey) {
					res = this.execCommand('outdent');
				} else {
					res = this.execCommand('indent');
				}
				
				if (res) {
					this._keydown_tmp_counter = 0; //reset counter
					return false;
				}
			}
			
			//改行直後のTAB
			if(this._last_keycode == 13){
					
				var _code = this.getCaretCode();
				code = $(_code);
				caret = this.getCaret();	//insert caret node
				
				while(ele.nodeType == 3 /* Node.TEXT_NODE */
				|| ele.tagName.match(/span/i)){
					if(ele.parentNode.tagName && ele.parentNode.tagName.match(/body/i)){
						this.wrapTextNode(ele);
					}
					ele = ele.parentNode;
				}
				
				//改行→TABは常に新しい段落とする
				if ($(ele).css("display") == "block") {
					try {
						left = parseInt($(ele).css("marginLeft"));
						if (isNaN(left)) {
							left = 20;
						}else{
							left += 20;
						}
						
						//ノードの後ろに追加
						p = this.splitToParagraph(ele);
						p.css("marginLeft", left + "px");
						
						$(".aobata_last_insert_br", this.getWindow().document).remove();
						
						return false;
					}catch(e){
						$(caret).remove();
						return false;
					}
				}
				
				//clear
				this.removeCaret();
			}
			
			
			if (event.shiftKey) {
				var node = this.getCaret();
				var target = node.previousSibling;
				
				if (!target) {
					$(node).remove();
					return;
				}
				
				if(target.nodeType == 3 /* Node.TEXT_NODE */){
					target = target.parentNode;
					$(target).after(node);
				}
				
				if($(target).hasClass("aobata_tab")){
					$(target).remove();
				}
				
				this._keydown_tmp_counter = 0;	//reset counter
				this.removeCaret();
				
			}else{
				//現在の位置に<span class="aobata_tab" style="white-space:pre">\t</span>を挿入
				this.insertHTML('<span class="aobata_tab" style="white-space:pre">&nbsp;&nbsp;&nbsp;&nbsp;</span>');
				this._keydown_tmp_counter = 0;	//reset counter
			} 
			return false;
			
		}/* event tab */
		
		/* ctrl + s */
		if((event.ctrlKey && event.keyCode == 83)
		|| (event.metaKey && event.keyCode == 83)
		){
			save_now();
			event.preventDefault();
			return false;
		}
		
		//when some keys(not enter, tab, backspace etc...) over 3 times, parameter windows will be hidden.
		if(this._keydown_tmp_counter >= 2){
			this.closeParam();
			this._keydown_tmp_counter = 0;
		}
		
		this.adjustSize(true);
	},
	
	keyup : function(event){
		body = this.getWindow().document.body;
		
		if(body.childNodes.length == 1){
			event.preventDefault();
			
			if (body.firstChild.nodeType == 3
			|| body.firstChild.tagName.match(/br/i)) {
				var range = this.getRange();
				var ele = body.firstChild;
				
				p = $("<p></p>", this.getWindow().document);
				if (ele.nodeType == 3) {
					p.append(ele);
				}
				
				$(this.getWindow().document.body).html(p);
				
				
				caret = this.getCaret();
				p.append(caret);
				range.move(caret);
				this.removeCaret();
			}
		}
	},
	
	saveCaret : function(){
		this.caret = this.getRange();
	},

	getCaret : function(){
		var caret = this.getWindow().document.getElementById("__caret");
		if(caret){
			$(caret).remove();
		}
		
		try {
			var range = this.getRange();
			range.paste('<span id="__caret">___aobata___</span>');
			caret = this.getWindow().document.getElementById("__caret");
			range.sel(caret);
		}catch(e){
			//do nothing
		}
		
		return caret;
	},
	
	getCaretNode : function(){
		return this.getWindow().document.getElementById("__caret");
	},
	
	getCaretCode : function(){
		return '<span id="__caret">___aobata___</span>' + "\u200B";
	},
	
	node : function(select){
		return $(select,this.getWindow().document);
	},
	
	insertHTML : function(html){
		var range = this.getRange();
		
		if (aobata_editor.is_IE) {
			range.paste('<span id="__caret">___insert___</span>' + html + "\u200B");
		}else{
			range.paste(html + '<span id="__caret">___insert___</span>' + "\u200B");
		}
		
		//resize inner
		this.adjustSize(true);
		
		this.removeCaret();
	},
	
	removeCaret : function(){
			
		var range = this.getRange();
		if (!range) {
			return false;
		}
		
		var caret = this.getWindow().document.getElementById("__caret");
		if(!caret)return false;
		
		//sync scrollTop
		this.scrollToElement(caret);

		range.caret(caret);
		
		this.getWindow().focus();
		
	},
	
	/**
	 * toggle wysiwyg <-> textarea
	 */
	swapMode : function(){
		this.sync();
		this.mode = (this.mode) ? false : true;
		this.textarea.toggle();
		this.frame.toggle();
		
		if (!this.mode) {
			this.closeParam();
		}
		
		if(this.mode){
			this.header.removeClass("text-mode").show();
			this.footer.show();
			this.frame.click();
			this.wrapper.find(".aobata_preview_overlay").show();
			this.header.find(".btn-html").removeClass("active");
		}else{
			this.textarea.focus();
			this.wrapper.find(".aobata_preview_overlay").hide();
			this.header.find(".btn-html").addClass("active");
		}
	},
	
	/**
	 * adjust size to iframe's content
	 */
	adjustSize : function(flag){
		if (flag && this.options.editable && !this.options.locked) {
			body = $(this.getWindow().document.body);
			max = ($(".section_list").size() < 2) ? 400 : 200;
			$(".article-body", this.wrapper).height(
				Math.max(max,body.height() + 15)
			);
		} else {
			$(".article-body", this.wrapper).height($(this.getWindow().document.body).height() + 15);
		}
	},
	
	/**
	 * adjust size to iframe's content
	 */
	collapseSize : function(flag){
		if (flag && this.options.editable) {
			$(".article-body", this.wrapper).height(200);
		} else {
			$(".article-body", this.wrapper).height(
				Math.min(200,$(this.getWindow().document.body).height() + 15)
			);
		}
	},
	
	/**
	 * @TODO
	 */
	scrollToElement : function(ele){
		return true
	},
	
	/* wysiwyg */
	
	addCommand : function(a, func){
		if(!this.command[a]){
			this.command[a] = [];
		}
		this.command[a].push(func);
	},
	
	fireCommand : function(a,b,c){
		_func = this.command[a];
		try {
			if (this._currentfocuselement) {
				res = false;
				for (var i in _func) {
					res = _func[i].apply(this, [this._currentfocuselement, b, c]);
				}
				
				return res;
			}
		}catch(e){
			//do nothing
		}
		
		return false;
	},
	
	execCommand : function(a,b,c){
		var res = false;
		
		//fix bug for IE( when paramView is clicked, iframe lost it's focus)
		if(aobata_editor.is_IE && this.caret){
			try {
				this.caret.select();
				node = this._currentfocuselement = this.caret.start()[0];
			}catch(e){
				//do nothing
			}
		}
		
		//command is always lower case.
		a = a.toLowerCase();
		
		try {
			if (this.command[a] != undefined) {
				res = this.fireCommand(a, b, c);
			}
			
			if (res === false) {
				this.getWindow().document.execCommand(a, b || false, c || null);
			}
		}catch(e){
			res = false;
		}
		
		
		if(a.match(/(backcolor|forecolor|unlink)/i)){
			this.closeParam();
		}
		
		if(!res){
			return false;
		}
		
		return res;
	},
	
	exec : function(func){
		this.getWindow().focus();
		
		//選択範囲を作成
		var range = this.getRange();
		
		//do func
		var res = func(range);
		
		//htmlの挿入
		range.paste(res + '<span id="__caret">___exec___</span>');
		
		//カーソルを末尾に
		var range = this.getRange();
		range.caret(this.getWindow().document.getElementById("__caret"));
		
		if(!range)return false;
		
		this.getWindow().focus();
		
		return true;
	},
	
	execNode : function(func){
		if (this._currentfocuselement) {
			func(this._currentfocuselement);
		}
	},
	
	/* util */
	
	getRange : function(d){
		d = (d) ? d : this.getWindow().document;
		var select = (d.selection) ? d.selection : this.getWindow().getSelection();
		return $.range(select,d);
	},
	
	onReturnFocus : function(){
		//fix bug for IE( when paramView is clicked, iframe lost it's focus)
		if(aobata_editor.is_IE && this.caret){
			this.caret.select();
			node = this._currentfocuselement = this.caret.start()[0];
		}
	},
	
	wrapTextNode : function(ele){
		$(ele).wrap("<p></p>");
		$(ele).parent().append($(this.getCaretCode()));
		this.getCaret();
	},
	
	splitToParagraph : function(ele){
		var _code = this.getCaretCode(),range = this.getRange();
		code = $(_code);
		this.getCaret();	//insert caret node
		
		html = $(ele).html();
		pos = html.indexOf(code.html()) + (code.html() + "</span>").length;
		
		html1 = html.substr(0,pos);
		html2 = html.substr(pos);
		$(ele).html(html1);
		this.removeCaret();
		
		if(html2.length < 1){
			html2 = "\u200B";
		}
		
		var html2 = $("<p>\u200B</p>").html(html2);
		caret = this.getCaret();
		$(ele).after(html2);
		html2.prepend(caret);
		range.move(caret);
		this.removeCaret();
		
		return html2;
	},
	
	insertNewParagprah : function(ele){
		var p = $("<p>\u200B</p>"),range = this.getRange();;
		caret = this.getCaret();
		
		if ($(ele).get(0).tagName.match(/body/)) {
			$(ele).append(p);
		}else{
			$(ele).after(p);
		}
		
		p.append(caret);
		range.move(caret);
		this.removeCaret();
		
		return p;
	},
	
	insertBreakLine : function(){
		this.node(".aobata_last_insert_br").removeClass("aobata_last_insert_br");
		this.insertHTML("<br class=\"aobata_last_insert_br\" />");
	},
	
	/**
	 * @param {string} url
	 */
	convertUrlByBase : function(url, flag){
		
		if(!url)return url;
		if(url.length < 1)return url;
		
		base_url = aobata_editor.option.base_url;
		if(base_url.length < 1){
			if (location.origin) {
				base_url = location.origin + "/";
			} else {
				var _tmp = new Image;
				_tmp.src = "/";
				base_url = _tmp.src;
			}
		}
		
		
		//end of base_url is always "/"
		if(base_url[base_url.length -1] != "/")base_url += "/";
		
		//http -> / 
		if(flag){
			url = url.replace(base_url, "/");
		
		// / -> http
		}else{
			
			//if relative url
			if(url[0] == "."){
				url = base_url + url;
			}
			
			//if start with http
			if(url.match(/^https?:/)){
				return url;
			}
			
			url = base_url + url.substring(1);
			
		}
		
		return url;
	}
	
};

$(function(){
	aobata_editor.prepare();
	
	$(".aobata_editor").each(function(){
		new aobata_editor($(this));
	});
	$(".aobata_preview").each(function(){
		new aobata_editor($(this),{editable:false});
	});
	$(".aobata_display").each(function(){
		new aobata_editor($(this),{editable:false,locked:true});
	});
});
