(function(){
	var toggle_dynamic_proof_mode = function(){
		var _layer = document.getElementById("dynamic-proofreading-layer");
		var _window = document.getElementById("dynamic-proofreading-window");
		if(!_layer){
			_layer = document.createElement("div");
			_layer.setAttribute("id","dynamic-proofreading-layer");
			document.body.appendChild(_layer);
			
			_window = document.createElement("div");
			_window.setAttribute("id","dynamic-proofreading-window");
			document.body.appendChild(_window);
			
			var close_btn = document.createElement("p");
			close_btn.setAttribute("class","close");
			_window.appendChild(close_btn);
			close_btn.onclick = function(){
				toggle_dynamic_proof_mode();
			};
			
			var iframe = document.createElement("iframe");
			iframe.setAttribute("src",SOYCMS_TASK_URL);
			_window.appendChild(iframe);
		}
		
		if(_layer.style.display == "none" || !_layer.style.display){
			_layer.style.display = "block";
			_window.style.display = "block";
			
			_window.style.marginLeft = -1 * _window.offsetWidth / 2 + "px";
			_window.style.marginTop = -1 * _window.offsetHeight / 2 + "px";
		}else{
			_layer.style.display = "none";
			_window.style.display = "none";
		}
		
	};
	
	document.getElementById("dynamic-proofreading").onclick = function(){
		toggle_dynamic_proof_mode();
	};
//	$(".dynamic-proofreading-section").each(function(){
//		var position = $(this).attr("data-position").split("x");
//		$(this).css({
//			top : position[0] * 1,
//			left : position[1] * 1
//		});
//	});
})();