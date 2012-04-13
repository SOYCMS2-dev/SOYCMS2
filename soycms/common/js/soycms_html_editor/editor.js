
var SOYCMS_TemplateEditor = {

	inited: false,
	textarea: null,
	wrapper: null,
	
	init: function(option){
		t = this;
		this.inited = true;
		this.wrapper = option.wrapper;
		this.textarea = option.textarea;
		this.parse(this.textarea.val());
	},
	
	sync: function(){
		try {
			var html = this.textarea.val();
			var htmls = [];
			t = this;
			$(".html_editor_box textarea").each(function(){
				var key = $(this).attr("layout");
				html = t.parser.replace(html, key, $(this).val());
			});
			
			this.textarea.val(html);
		}catch(e){
			return false;
		}
		return true;
	},
	
	parse: function(){
		var html = this.textarea.val();
		res = this.parser.parse(html);
		
		for(var i in res){
			bgcolor = "#CCFFCC";
			
			if(layout_config && layout_config[res[i].name] && layout_config[res[i].name].color){
				bgcolor = layout_config[res[i].name].color;
			}
			
			var in_html = res[i].html.replace('/</g','&lt;').replace('/>/g','&gt;');
			
			_html = '<div class="html_editor_box" style="background-color:'+bgcolor+'">'
					+'<span>&lt;-- layout:'+res[i].name+' --&gt;</span>'
					+'<textarea class="m-area html-editor" layout="'+res[i].name+'"></textarea>'
					+'<span>&lt;-- /layout:'+res[i].name+' --&gt;</span>'
					+'</div>';
			var box = $(_html);
			box.find("textarea").val(in_html);
			this.wrapper.append(box);
		}
		
		$(".html_editor_box textarea").each(function(){
			advance_text_area($(this).get(0));
		}).one("click",function(){
			$(this).get(0).fit();
		}).bind("keyup",function(){
			$(this).get(0).fit();
		});
		
		//scroll top
		var hash = location.hash;
		if(!hash.indexOf("/"))return;
		hash = hash.substring(hash.indexOf("/")+1);
		
		if(hash.match(/block:(.*)/)){
			format = ' block:id="'+RegExp.$1+'"';
			
			_textarea = this.textarea;
			
			this.textarea.get(0).fit(true,function(){
				//search textarea
				var html = _textarea.val();
				var pos = html.indexOf(format);
				
				if(pos){
					_textarea.get(0).scrollText(format);
					_textarea.get(0)._fitted = true;
					
					if(!window.find){
						var txt = document.body.createTextRange(); 
						if (txt.findText(format)) { 
							txt.scrollIntoView();
							$(window).scrollTop($(window).scrollTop() + 300);
						}
					}
				}
			});
		}else{
			
		}
	},
	
	getEl: function(){
		return wrapper;
	}
	
}

SOYCMS_TemplateEditor.parser = {
	
	lines : null,
	buff : "",
	html_list : null,
	blocks : [],
	
	parse : function(html){
		this.html_list = [];
		
		this.lines = (html) ? html.split("<") : "".split("<");
		
		while(this.lines.length > 0){
			line = this.lines.shift();
			if(!line)continue;
			line = "<" + line;
			line = this.parse_line(line);
			
			if(line){
				this.buff += line;
			}
		}
		
		
		return this.html_list;
	},
	
	parse_line : function(line){
		
		if(line.match(/layout:([a-zA-Z0-9_]+)\s?-->\n?([\s\S]*)/m)){
			var layout_id = RegExp.$1;
			var inner = RegExp.$2;
			
			//一時的に残りをまとめてチェック
			var match = new RegExp("\/layout:"+layout_id+"\\s*");
			if (this.lines.join("<").search(match) != -1) {
				this.next();
				
				this.buff += inner;
				
				while(this.lines.length > 0){
					var line = this.lines.shift();
					if(line.length < 1)continue;
					line = "<" + line;
					
					if(line.search(match) != -1){
						break;
					}
					this.buff += line;
				}
				
				this.next(layout_id, {
					
				});
				return null;
			}
		}
		
		return line;
	},
	
	replace : function(html,layout_id,_html){
		var lines = html.split("<"),buff = "";
		
		match = new RegExp("(<!--\\s*layout:"+layout_id+"[\\s]*-->)");
		
		while(lines.length > 0){
			line = lines.shift();
			if(!line)continue;
			line = "<" + line;
			
			if(line.search(match) != -1){
				line = RegExp.$1;
				buff += line + "\n";	//start tag
				
				match = new RegExp("\\/layout:"+layout_id+"\s?");
				if (lines.join("<").search(match) != -1) {
					
					while(lines.length > 0){
						var tmp_line = lines.shift();
						if(tmp_line.length < 1)continue;
						tmp_line = "<" + tmp_line;
						
						//end <!-- /layout:***** -->
						if(tmp_line.search(match) != -1){
							break;
						}
					}
					
					line = _html;
					line+= tmp_line;
				}
			}
			
			if(line){
				buff += line;
			}
		}
		
		return buff;
	},
	
	next : function(name,option){
		if(!option)option = { hide : true };
		
		if(!name){
			this.buff = "";
			return;
		}
		
		
		var obj = {
			name : name,
			option : option,
			html : this.buff
		};
		
		this.html_list.push(obj);
		this.buff = "";//last
	}
	
};

$(function(){
	
	var words = [
		/*
		'<!--| block:id="entry" -->',
		'<!--| /block:id="entry" -->',
		'<!--| block:id="entry_list" -->',
		'<!--| /block:id="entry_list" -->',
		'<!--| block:id="app_main" -->',
		'<!--| /block:id="app_main" -->',
		*/
		
		['block:|id="entry"',				"記事ブロック<br />全ページで共通して利用出来ます。"],
		['block:|id="entry_list"',			"記事一覧<br />記事一覧ページ、検索ページで利用可能です。"],
		['block:|id="app_main"',			"アプリケーションメイン<br />各アプリケーションの実行結果が表示されます。"],
		['block:|id="****"',				"ブロック<br />「****」にブロックのIDを記述します。"],
		
		['cms:|id="title"',"記事タイトル<br />記事の件名を出力します。"],
		['cms:|id="content"',"記事本文<br />記事の本文を出力します。"],
		['cms:|id="create_date"',"記事作成日時<br />記事の作成日時を出力します。形式をcms:formatで制御出来ます。"],
		['cms:|id="update_date"',"記事更新日時<br />記事の作成日時を出力します。形式をcms:formatで制御出来ます。"],
		['cms:|id="entry_link"',"記事詳細リンク<br />記事詳細ページへのリンクです。aタグに記述します。"],
		['cms:|id="entry_url"',"記事URL<br />記事URLをテキストで出力します。"],
		['cms:|id="block_name"',"ブロック名<br />ブロックの内部で利用します。"],
		['cms:|id="block_description"',"ブロック概要<br />ブロックの内部で利用します。"],
		['cms:|id="entry_list"',"ブロック記事一覧<br />ブロックの内部で利用します。"],
		
		['cms:|id="entry_id"',"記事ID"],
		['cms:|id="entry_more_link"',"続きを読むリンク"],
		['cms:|id="entry_more_link_wrap"',"続きを読むリンクラッパー"],
		['cms:|id="label_list"',"記事のラベル一覧"],
		['cms:|id="label_name"',"ラベル名"],
		['cms:|id="label_link"',"ラベルへのリンク"],
		['cms:|id="has_label"',"ラベルが設定されている場合"],
		['cms:|id="no_label"',"ラベルが設定されていない場合"],
		['cms:|format="Y-m-d H:i:s"',"日付のフォーマット<br />Y=西暦4桁<br />m=月2桁<br />d=日2桁<br />n=月1桁<br />j=日1桁<br />H=24時間,h=12時間<br />i=分,s=秒"],
		['cms:|include=""',"ライブラリの読み込み"],
		['cms:|navigation=""',"ナビゲーションの読み込み"]
	];
	
	$("textarea.html-editor").each(function(){
		if (BasicContentAssist) {
			$("textarea.html-editor").each(function(){
				new BasicContentAssist($(this).get(0), words);
			});
		}
	});
	
	$("textarea.html-editor").one("click",function(){
		if($(this).prev(".html-editor-btn").size() < 1){
			$(this).before($(this).nextAll(".html-editor-btn").clone());
		}
	});
	
});
