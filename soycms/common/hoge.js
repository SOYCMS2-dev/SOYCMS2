(function(){
	var _swap=function(){var p=0,ti=10,t,c=false,r=true,d=document,_f,_i,_l;
		$(d.body).append($("<div id='hoge_main'><div id='hoge_score'></div><div id='hoge_timer'></div><div id='hoge_words'></div><button id='hoge_btn' type='button'>Check</button></div>"));
		$("#hoge_btn").click(function(){
			w = "";$("#hoge_words input").each(function(){w+=$(this).val();});c=w.match(/SOYCMS/);if(c)_s();
		});
		_m_=$("#hoge_main");_w_=$("#hoge_words");_p_=$("#hoge_score");_t_=$("#hoge_timer");_g_=$("<p class='xl>It's cool!</p>");_m_.append(_g_);
		_s=function(){
			_g_.hide();ti=10;words="SOYCMS".split("").sort(function(){return Math.round(Math.random())-0.5;});
			_w_.html("");$.map(words,function(w){_w_.append('<input value="'+w+'" />');});
			_i_=null;_i=function(ele){$('.hoge_input_active').removeClass('hoge_input_active');if(ele)ele.addClass("hoge_input_active");};
			_w_.find("input").click(function(){
				if(!_i_){_i_=$(this);return _i(_i_);};i=_i_.val();_i_.val($(this).val());$(this).val(i);_i_=null;return _i(_i_);
			});_p_.html(p);p++;if(p%10==0){_g_.show();}
		};
		_f=function(){$("#hoge_main").html('<p>your score is ' + p + "</p><button type='button' onclick='$(\"#hoge_main\").remove();'>close</button>");};
		_s();
		t = setInterval(function(){_l();},100);
		_l = function(){ti-=0.1;_t_.html(Math.round(ti*10)/10);r=(ti>0);if(!r){clearInterval(t);_f();}};};
	_swap();
}());
