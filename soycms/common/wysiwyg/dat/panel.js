//
$(function(){
	$.fn.toggleFade = function(){
		return this.each(function(){
			if($(this).is(":hidden")){
				$(this).fadeIn();				
			}else{
				$(this).fadeOut();
			}
		});
	};
	
	$('input.input-link')
			.focus(function() { $(this).addClass("active") })
			.blur(function() { if ($(this)[0].value == '') { $(this).removeClass("active") } });
	
	
	$(".downmenu-layer").live("click",function(event){
		$(this).toggleFade();
	});
	
	$(".toggle-btn").live("click",function(){
		$(this).toggleClass("active");
	});
	
});

aobata_editor.hideAllPopup = function(){
	$(".downpanel-layer,.downmenu-layer").hide();
	$(".downpanel-fix").hide();
	$(".panel-parts").css("zIndex",50);	//for ie bug
	
};;

aobata_editor.showPopup = function(ele){
	return $(ele).show() 
		.find(".panel-parts").css("zIndex",2000000);
	return $(ele);
};

aobata_editor.showOption = function(ele){
	
	var option = $("#" + $(ele).parents(".downpanel").attr("id") + "_option");
	
	is_visible = option.is(":visible");
	aobata_editor.hideAllPopup();
	
	if(is_visible){
		return false;
	}
	
	if (is_visible && !aobata_editor.is_IE) {
		return false;
	}
	
	//表示する
	$(ele).parents(".panel-parts").css("zIndex",2000000);
	
	aobata_editor.showPopup(option).css({
		left : 0,
		top : 29
	});
	
};

/*
 * aobata_editor
 * 
 */
var SectionController = {
	
	moveUp : function(ele){
		
		aobata_editor.syncAll();
		
		var parent = this.getEl(ele);
		
		tgt1 = parent;
		tgt2 = parent.prev(".section_list");
		if(tgt2.size()<1)return;
		
		aobata_editor.close();
		
		$(window).scrollTop(tgt2.offset().top - 50);
		tgt2.before(parent);
		this.rebuild();
		
		return;
	},
	
	moveDown : function(ele){
		aobata_editor.syncAll();
		
		var parent = this.getEl(ele);
		
		tgt1 = parent;
		tgt2 = parent.next(".section_list");
		if(tgt2.size()<1)return;
		
		aobata_editor.close();
		
		$(window).scrollTop(tgt1.offset().top + tgt2.height() - 50);
		tgt2.after(parent);
		this.rebuild();
		
		
		this.rebuild();
		
	},
	
	remove : function(ele){
		this.getEl(ele).find(".article-body").css("opacity","0.5");
		this.getEl(ele).addClass("section_removed");
		this.getEl(ele).find(".section_remove").val(1);
	},
	
	cancelRemove : function(ele){
		this.getEl(ele).find(".article-body").css("opacity","1");
		this.getEl(ele).removeClass("section_removed");
		this.getEl(ele).find(".section_remove").val(0);
	},
	
	getEl : function(ele){
		$("#sections_container > p").remove();
		return $(ele).parents(".section_list");
	},
	
	rebuild : function(){
		//last section's footer is always visible
		$(".last-section").removeClass("last-section");
		$(".article-footer").hide();
		$(".section_list:last").addClass("last-section");
		//$(".last-section .article-footer").show();
	}
	
	
};