$(function(){
	$(".entry_check").click(function(){
		$(".entry_check_option").show();
	});
	$(".set_trash").click(function(){
		var checked = $("input.entry_check:checked");
		var data = $.map(checked,function(n,i){
			return $(n).val();	
		});
		
		common_start_loading($(this));
		
		$.post( SOYCMS_ROOT_URL + "site/entry/operation", "mode=trash&directory=" +$("#directory_id").val() +"&entryIds=" + data.join(",") + "&all=" + $("#entry_all:checked").val(), 
			function(res){
				common_stop_loading();
				
				checked.parents("tr").addClass("entry_trashed");
				$(".trash_count").html(res).parent().effect("highlight", {}, 3000);
			}
		);
	});
	$(".set_open,.set_close").click(function(){
		var checked = $("input.entry_check:checked");
		var data = $.map(checked,function(n,i){
			return $(n).val();	
		});
		
		mode = ($(this).hasClass("set_open")) ? "open" : "close";
		
		common_start_loading($(this));
		
		$.post( SOYCMS_ROOT_URL + "site/entry/operation", "mode="+mode+"&directory=" +$("#directory_id").val() +"&entryIds=" + data.join(",") + "&all=" + $("#entry_all:checked").val(), 
			function(res){
				common_stop_loading();
				
				if (res == 1) {
					location.reload();
				}else{
					alert(res);
				}
			}
		);
	});
	
	$(".up_btn,.down_btn").click(function(){
		$.get($(this).attr("href"),function(res){
			
		});
	});
	
	if(SOYCMS_CURRENT_DIRECTORY){
		$("#page-" + SOYCMS_CURRENT_DIRECTORY + " > dl").addClass("new-directory");
	}
	
	
});
