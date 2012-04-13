
(function(){
	
	aobata_editor.plugin.register("common", function(editor){
		
		editor.onGetHTML.add(function(editor,html){
			
			//remove first \n repat
			html = html.replace(/^\n+/g,"");
			
			//
			html = html.replace("\u200B","");
			html = html.replace(/^[\n\r]+/g,"");
						
			//remove last \n repeat
			html = html.replace(/\n+$/g,"\n");
			
			html = html.replace(/<(p|div)>[\n\r]*<\/(p|div)>/g,"");
			
			this.argument = html;
			
			return html;
		});
		
	});
	
}());
