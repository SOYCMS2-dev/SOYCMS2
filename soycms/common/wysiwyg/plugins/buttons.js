
(function(){
	
	aobata_editor.plugin.register("buttons", function(editor){
		
		editor.addCommand("imagelink" , function(node,url){
			if (node.tagName.toLowerCase() != "img") {
				return;
			}
			
			var link = $(node).parents("a");
			
			if(link.size() < 1 && url.length > 0){
				$(node).wrap("<a></a>");
				link = $(node).parents("a");
			}
			
			if(url.length > 0){
				link.attr("href",url);
			}else{
				link.after($(node)).remove();
			}
			
			return true;
		});
		
		editor.addCommand("css", function(node,key,value){
			try {
				range = this.getRange();
				if(range.collapsed){
					return;
				}
				if(value.length < 1)value = null;
				
				elements = range.elements();
				
				for (var i = 0; i < elements.length; i++) {
					$(elements[i]).css(key, value);
				}
				
				$(elements[elements.length-1]).after('<span id="__caret"></span>');
				t.getWindow().focus();
				t.removeCaret();
				t.paramView.hide();
			}catch(e){
				alert(e);
			}
			
			return true;
		});
		
		//add class
		editor.addCommand("class" , function(node,class_name,value){
			
			try {
				range = this.getRange();
				if(range.collapsed){
					return;
				}
				
				elements = range.elements();
				
				for (var i = 0; i < elements.length; i++) {
					if (!class_name || class_name.length < 1) {
						$(elements[i]).attr("class","");
						continue;
					}
					
					if($(elements[i]).parent().hasClass(class_name)){
						$(elements[i]).parent().removeClass(class_name);
						continue;
					}
					
					$(elements[i]).addClass(class_name);

				}
				
				$(elements[elements.length-1]).after('<span id="__caret"></span>');
				t.getWindow().focus();
				t.removeCaret();
				t.paramView.hide();
			}catch(e){
				
			}
			
			return true;
		});
		
		
		//alt class
		editor.addCommand("alt_class" , function(node,class_name,old_classes){
			
			try {
				range = this.getRange();
				if(range.collapsed){
					return;
				}
				
				elements = range.elements();
				
				for (var i = 0; i < elements.length; i++) {
					
					if (!class_name || class_name.length < 1) {
						$(elements[i]).attr("class","");
						continue;
					}
					
					if($(elements[i]).parent().hasClass(class_name)){
						$(elements[i]).parent().removeClass(class_name);
						continue;
					}
					
					for(var j in old_classes){
						if($(elements[i]).parents().hasClass(old_classes[j])){
							$(elements[i]).parents().removeClass(old_classes[j]);
						}
					}
					
					$(elements[i]).addClass(class_name);

				}
				
				$(elements[elements.length-1]).after('<span id="__caret"></span>');
				t.getWindow().focus();
				t.removeCaret();
				t.paramView.hide();
			}catch(e){
				
			}
			
			return true;
		});
		
		
		editor.addCommand("createlink" , function(node,b,c){
			
			if($(node).parents("a").size() > 0){
				node = $(node).parents("a")[0];
			}
			
			if (node.tagName.toLowerCase() == "a") {
				$(node).attr("href",c);
				return true;
			}
			
			return false; //exec default
		});
		
		editor.addCommand("unlink", function(node, b, c){
			
			if($(node).parents("a").size() > 0){
				node = $(node).parents("a")[0];
			}
		
			if (node.tagName.match(/a/i)) {
				$(node).after($(node).html()).remove();
			}
			
			return false; //exec default
		});
		
		editor.addCommand("formatblock", function(node, b, c){
			
			if (c != "p") {
				var range = this.getRange();
				node = $(node);
				while (node[0].nodeType == 3 /* Node.TEXT_NODE */) { //TEXT NODE
					node = $(node).parent();
				}
			}
			
			res = this.getWindow().document.execCommand("formatblock", b || false, c || null);
			
			//fix webkit bug
			if(c != "p"){
				$(c + " br",this.getWindow().document).remove();
			}
			
			return true;
		});
		
		editor.addCommand("indent", function(node, b, c){
			
			node = $(node);
			
			while (node[0].nodeType == 3 /* Node.TEXT_NODE */ ||
			node[0].tagName.match(/span/i)) { //TEXT NODE
				if (node[0].parentNode.tagName && node[0].parentNode.tagName.match(/body/i)) {
					$(node).wrap("<p></p>");
				}
				node = $(node).parent();
			}
			
			if (node[0].tagName.match(/body|ul|ol|dl|li|dt|dd/i)) {
				return true;
			}
			
			left = parseInt(node.css("marginLeft"));
			if (isNaN(left)) left = 0;
			left += 20;
			node.css("marginLeft", left);
			
			return true;
		});
		
		editor.addCommand("outdent", function(node, b, c){
			
			
			node = $(node);
			while (node[0].nodeType == 3 /* Node.TEXT_NODE */) { //TEXT NODE
				node = $(node).parent();
				if(node.css("display") == "inline"){
					node = $(node).parent();
				}
			}
			
			if(node[0].tagName.match(/body|ul|ol|dl|li|dt|dd/i)){
				return true;
			}
			
			left = parseInt(node.css("marginLeft"));
			if(isNaN(left))left = 0;
			left -= 20;
			if (left < 0) {
				left = 0;
				node = $(node).parent();
			}
			node.css("marginLeft",left);
			if(left == 0){
				node.css("marginLeft","");
				node.css("marginLeft",null);
			}
			
			return true;
		});
		
		editor.addCommand("removeformat", function(node, b, c){
			
			range = this.getRange();
				
			if (!range.collapsed) {
				elements = range.elements();
				
				for (var i = 0; i < elements.length; i++) {
					node = $(elements[i]);
					node.find("span").each(function(){
						$(this).after($(this).html()).remove();
					});
					node.find("*").removeAttr("style");
					node.removeAttr("style");
				}
			}
			
			if(this._currentfocuselement){
				$(this._currentfocuselement).find("*").removeAttr("style").removeAttr("color").removeAttr("bgcolor");
			}
			
			return false; //exec default
			
		});
		
		editor.addCommand("tablecontext" , function(node, b, c){
			
			this.tableContextMenu.css("top", c.y);
			this.tableContextMenu.css("left", c.x);
			
			aobata_editor.table_helper.cell = $(node);
			
			this.tableContextMenu.show();
			
			return true;
		});
	});
	
}());
