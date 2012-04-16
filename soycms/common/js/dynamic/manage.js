if(typeof dynamic_manage_templates != "undefined"){
(function(){
	
	var wrap =  $("<div id='dynamic-edit-menu-wrap'></div>").hide();
	$("body").append(wrap);
	
	var build_box = function(caption, list){
		var box = $("<div></div>").attr("class","dynamic-edit-menu-box");
		var tree = $("<ul></ul>");
		box.append("<h2>"+caption+"</h2>");
		box.append(tree);
		
		for(var i in list){
			if(!list.hasOwnProperty(i))continue;
			var obj = list[i];
			var li = $("<li></li>").append($("<em>&nbsp;</em>")).append($("<a></a>").attr("href",obj.link).attr("target","dynamic").html(obj.label));
			tree.append(li);
		}
		
		wrap.append(box);
	}
	
	
	if(dynamic_manage_templates.template){
		build_box("Template", dynamic_manage_templates.template);
	}
	if(dynamic_manage_templates.navigation){
		build_box("Navigation", dynamic_manage_templates.navigation);
	}
	if(dynamic_manage_templates.template){
		build_box("Library", dynamic_manage_templates.library);
	}
	
	
	$(document.body).bind("dblclick",function(){
		wrap.toggle();
	});
	
}());
}