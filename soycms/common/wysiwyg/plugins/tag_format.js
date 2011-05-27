
(function(){
	
	aobata_editor.plugin.register("tag_format", function(editor){
		
		editor.onGetHTML.add(function(editor,html){
			
			//b -> strong
			html = html.replace(/<(\/?)b(\s.*)?>/gm,"<$1strong$2>");
			
			//s -> del
			html = html.replace(/<(\/?)s(\s.*)?>/gm,"<$1del$2>");
			
			//namespaced tag -> convert
			html = html.replace(/<(\/?)[a-z0-9]+:([a-z0-9]+)(\s.*)?>/gm,"<$1$2$3>");
			
			var replace_not_quote_attribute = function(obj){
				var zz = obj.innerHTML
				,z = zz.match(/<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|'.*?'|[^'">\s]+))?)+\s*|\s*)\/?>/g);
				
				if (z){
					
					for (var i=0;i<z.length;i++){
						var y
						, zSaved = z[i]
						, attrRE = /\=[a-zA-Z\.\:\[\]_\(\)\&\$\%#\@\!0-9]+[?\s+|?>]/g;
						z[i] = z[i].replace(/(<?\w+)|(<\/?\w+)\s/,function(a){return a.toLowerCase();});
						y = z[i].match(attrRE);//deze match
						
						if (y){
							var j = 0,len = y.length
							while(j<len){
								var replaceRE = /(\=)([a-zA-Z\.\:\[\]_\(\)\&\$\%#\@\!0-9]+)?([\s+|?>])/g;
								var replacer = function(){
									var args = Array.prototype.slice.call(arguments),
									value = args[2].toLowerCase(),
									suffix = args[3];
									
									return '="'+ value + '"'+ suffix;
								};
								
								//replace
								z[i] = z[i].replace(y[j],y[j].replace(replaceRE,replacer));
								j++;
							}
						}
						zz = zz.replace(zSaved,z[i]);
					}
				}
				return zz;
			};
			
			
			html = replace_not_quote_attribute($("<div></div>").html(html).get(0));
			
			//all style value -> lower case
			html = html.replace(/style="([^"]+)"/gm,function(args){
				return args.toLowerCase();
			});
			
			//erase all temporary classes
			html = html.replace(/\s*class="([^"]*)"/g,function(args){
				res = args.replace(/(Apple\-|apple-|aobata_)[^"]+/, "");
				if(res.match(/\s*class=""/))return "";
				return res;
			});
			
			//all empty element tag replace
			var empty_tags = ["base","basefont","br","col","frame","hr","img","input","isindex","link","meta","param"];
			empty_tag_regexp = new RegExp("<("+empty_tags.join("|")+")([^>]*[^\\/])?>","ig");
			if(html.match(empty_tag_regexp)){
				html = html.replace(empty_tag_regexp,'<$1$2 />');
			}
			
			this.argument = html;
			return html;
		});
		
		editor.beforeGetHTML.add(function(editor,body){
			
			//all font tag convert to span
			$(body).find("font").each(function(index){
				try {
					ele = $(this);
					var span = $("<span></span>");
					span.html($(ele).html());
					$(ele).after(span);
					
					if ($(ele).attr("style")) span.attr("style", $(ele).attr("style"));
					if ($(ele).attr("color")) span.css("color", $(ele).attr("color"));
					if ($(ele).attr("bgcolor")) span.css("backgroundColor", $(ele).attr("bgcolor"));
					
					$(ele).remove();
				}catch(e){
					
				}
			});
			
			var marge_span = function(span){
				parent = $(span);
				child = $(span.firstChild);
				
				if(parent.attr("id") && child.attr("id"))return;
				_class = [];
				
				
				
				
			};
			var has_value = function(obj){
				return _ele.attr("id").length < 1
					&& _ele.attr("class").length < 1
					&& _ele.attr("style").length < 1
			}
			
			var format_element = function(ele,depth){
				var block_regex = /^(div|pre|blockquote|object)$/i,
				newline_regex = /^(p|div|pre|blockquote|ul|ol|dl|li|dd|dt|object|h[0-9])$/i,
				nonewline_regex = /^(h[0-9])/i,
				tag_name = (ele.tagName) ? ele.tagName.toLowerCase() : "";
				
				if(!depth)depth=0;
				
				try {
					
					//remove meta
					$("meta",ele).remove();
					
					//remove style attribute
					$(ele)
						.css("fontSize","")
						.css("fontStyle","")
						.css("fontFamily","");
					$(ele).find("*")
						.css("fontSize","")
						.css("fontStyle","")
						.css("fontFamily","");
					
					
					if($(ele).attr("style") != undefined && $(ele).attr("style").length == 0){
						$(ele).removeAttr("style");
					}
					
				//when comment tag
				}catch(e){
					return;
				}
				
				if(tag_name.match(/span/)){
					
					if(ele.innerHTML.length < 1){
						$(ele).remove();
						return;
					}
					
					_ele = $(ele);
					
					
					//span -> span
					if(ele.childNodes.length == 1
					&& ele.childNodes[0].nodeType != 3
					&& ele.childNodes[0].tagName.match(/span/i)){
						marge_span(ele);
						return;
					}
					
				}
				
				if (!ele.childNodes || ele.childNodes.length < 1){
					return;
				}
				
				//not newline_regex
				if(ele.nodeType != 3 /* Node.TEXT_NODE */
				&& tag_name.match(nonewline_regex)
				&& ele.childNodes.length > 0
				&& ele.childNodes[0].nodeType == 3 /* Node.TEXT_NODE */
				&& ele.childNodes[0].nodeValue.match(/^\n+/)
				){
					ele.childNodes[0].nodeValue = ele.childNodes[0].nodeValue.replace(/^\n+/,"");
				}
				
				for(var i=0;i<ele.childNodes.length;i++){
					
					if(ele.childNodes[i].nodeType == 3 /* Node.TEXT_NODE */)continue;
					
					//create new line
					if(ele.childNodes[i].tagName && ele.childNodes[i].tagName.match(newline_regex)){
						if (i == 0 || ele.childNodes[i - 1].nodeType != 3 /* Node.TEXT_NODE */) {
							ele.insertBefore(d.createTextNode("\n" + (new Array(depth)).join("\t")), ele.childNodes[i]);
							i++;
						}
					}
					
					format_element(ele.childNodes[i], depth + 1);
					
				}
				
				//end new line tags
				if (ele.tagName.match(block_regex)) {
					if(ele.innerHTML[ele.innerHTML.length -1] != "\n"){
						ele.innerHTML += "\n";
					}
				}
			};
			
			format_element(body);
			
		});
		
		editor.addCommand("format_indent" , function(node,html){
			
			var style_html = function(html_source,depth) {
				if(!depth)depth = 0;
				_depth = depth + 1;
				
				ele = $("<div></div>").html(html_source).get(0);
				
				for (var i = 0; i < ele.childNodes.length; i++) {
					_ele = ele.childNodes[i];
					if (_ele.innerHTML && _ele.innerHTML.match(/</)) {
						_ele.innerHTML = style_html(_ele.innerHTML, _depth);
					}
				}
				
				var tab = "";
				for(var i=0;i<depth;i++){
					tab += "  ";
				}
				
				html_source = ele.innerHTML;
				html_source = html_source.split("\n").join("\n" + tab);
				return html_source;
			};
			
			try {
				return style_html(html);
			}catch(e){
				alert(e);
			}
		});
		
		editor.addCommand("format_span" , function(node,html){
			
			while(true){
				var regexp = /<span([^>]*)><span([^>]*)>(.*)?<\/span><\/span>/;
				var _attr = "$1$2";
				if(!html.match(regexp)){
					break;
				}
				
				//
				try {
					attribues = [];
					_attribute = html.match(regexp)[0].replace(regexp, _attr);
					_attribute = _attribute.match(/(.*)="([^"]+)"/);
				}catch(e){
					throw e;
				}
				
				
				attributes = attribues.join(" ");
				html = html.replace(regexp,"<span"+attributes+">$3</span>");
			}
			
			//replace empty span
			html = html.replace(/<span>(.*?)<\/span>/gi,"$1");
			html = html.replace(/<span>/gi,"");
			
			return html;
		});
		
		
	});
	
}());
