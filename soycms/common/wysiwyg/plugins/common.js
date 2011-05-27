
(function(){
	
	aobata_editor.plugin.register("common", function(editor){
		
		editor.onGetHTML.add(function(editor,html){
			
			//remove first \n repat
			html = html.replace(/^\n+/,"");
			
			//
			html = html.replace("\u200B","");
			html = html.replace(/^[\n\r]+/,"");
						
			//remove last \n repeat
			html = html.replace(/\n+$/,"\n");
			
			this.argument = html;
			
			return html;
		});
		
	});
	
}());
