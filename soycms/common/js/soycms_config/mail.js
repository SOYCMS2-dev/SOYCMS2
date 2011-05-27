function changeSendPort(){
	var port = 25;
	if($("#is_use_ssl_send_server").attr("checked")){
		if($("#send_server_type_smtp").attr("checked"))port = 465;
	}
	
	$('#send_server_port').val(port);
}

function changeReceivePort(){
	var port = 110;
	if($("#is_use_ssl_receive_server").attr("checked")){
		if($("#receive_server_type_pop").attr("checked"))port = 995;
		if($("#receive_server_type_imap").attr("checked"))port = 993;
	}else{
		if($("#receive_server_type_pop").attr("checked"))port = 110;
		if($("#receive_server_type_imap").attr("checked"))port = 147;
	}
	
	$('#receive_server_port').val(port);	
}

function toggleSMTP(){
	var ids = [
		"send_server_address","send_server_port","send_server_user","send_server_password","is_use_ssl_send_server",
		"is_use_pop_before_smtp","is_use_smtp_auth",
		"receive_server_type_pop","receive_server_type_imap","receive_server_address","receive_server_port","receive_server_user","receive_server_password","is_use_ssl_receive_server"
	];
	if($("#send_server_type_smtp").attr("checked")){
		$(ids).each(function(key,id){
			$("#" + id).removeAttr("disabled");
		});
		disableUseSSL();
		togglePOPIMAPSetting();
		toggleSMTPAUTHSetting();
	}else{
		$(ids).each(function(key,id){
			$("#" + id).attr("disabled","disabled");
		});
	}
}

function toggleSMTPAUTHSetting(){
	if($("#is_use_smtp_auth").attr("checked")){
		$("#send_server_user").removeAttr("disabled");
		$("#send_server_password").removeAttr("disabled");

		$("#is_use_pop_before_smtp").attr("checked",false);
		togglePOPIMAPSetting();
	}else{
		$("#send_server_user").attr("disabled","disabled");
		$("#send_server_password").attr("disabled","disabled");
	}
}

function togglePOPIMAPSetting(){
	var ids = ["receive_server_type_pop","receive_server_type_imap","receive_server_address","receive_server_port","receive_server_user","receive_server_password","is_use_ssl_receive_server"];
	if($("#is_use_pop_before_smtp").attr("checked")){
		$(ids).each(function(key,id){
			$("#" + id).removeAttr("disabled");
		});

		disableUseSSL();
		disableUseIMAP();

		$("#is_use_smtp_auth").attr("checked",false);
		$("#pop_imap_config").slideDown();
		toggleSMTPAUTHSetting();
	}else{
		$(ids).each(function(id){
			$(id).attr("disabled","disabled");
		});
		$("#pop_imap_config").slideUp();
	}
}

function disableUseSSL(){
	if($("#is_ssl_enabled").val() == 0){
		$("#is_use_ssl_send_server").attr("disabled","disabled");
		$("#is_use_ssl_receive_server").attr("disabled","disabled");
	}
}

function disableUseIMAP(){
	if($("#is_imap_enabled").val() == 0){
		$("#receive_server_type_imap").attr("disabled","disabled");
	}
}

function confirm_test_send(){
	if($("#administrator_address").val().length < 1){
		alert("「管理者メールアドレス」を入力してください。");
		return false;
	};
	
	var addr = [$("#administrator_address").val()];
	
	if($("#test_mailaddress").val().length > 0){
		addr.push($("#test_mailaddress").val());
	}
	
	return confirm(
			"設定を保存してからその設定内容でテストメールを送信します。\n" + 
			"送信先: " + addr.join(",") + "\n\n"
			+ "よろしければ「OK」を押してください。\n"
			+ "中止する場合は「キャンセル」を押してください。"
			);
}

$(function(){
	$("#pop_imap_config").toggle($("#is_use_pop_before_smtp").attr("checked"));
});