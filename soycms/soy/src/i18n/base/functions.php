<?php
function init_site_i18n($lang = "ja"){
	//set soy2html language
	SOY2HTMLConfig::Language($lang);
	
	//language file directory
	$lang_dir = SOYCMS_SITE_DIRECTORY . ".i18n/";
	if(!file_exists($lang_dir))return;
	
	$domain = "default";
	
	$path = SOYCMS_SITE_DIRECTORY . ".cache/i18n/" . $lang . "/LC_MESSAGES/default.mo";
	if(!file_exists($path) && file_exists($lang_dir . $lang . ".mo")){
		@mkdir(dirname($path),0700,true);
		copy($lang_dir . $lang . ".mo", $path);
	}
	
	bindtextdomain($domain, SOYCMS_SITE_DIRECTORY . ".cache/i18n/");
	textdomain($domain);
	putenv('LC_ALL=' . $lang);
	//setlocale(LC_ALL, $lang);
}

function generate_site_i18n(){
	chdir(SOYCMS_SITE_DIRECTORY);
	
	exec('find . -iname "*.html" | xargs xgettext -o .i18n/messages.pot \
		--from-code=UTF-8 \
		-F \
		-k"_" \
		-k"__" \
		-k"_e"'
	);
	
	exec('find .plugin -iname "*.php" | xargs xgettext -o .i18n/messages.pot \
		--from-code=UTF-8 \
		-k"_" \
		-k"__" \
		-k"_e"'
	);
}

function generate_site_language_file($lang){
	chdir(SOYCMS_SITE_DIRECTORY);
	
	if(!file_exists(SOYCMS_SITE_DIRECTORY . ".i18n/" . $lang . ".po")){
		exec('copy .i18n/messages.pot i18n/'.$lang.'.po');
	}else{
		exec('msgmerge -U .i18n/messages.pot .i18n/'.$lang.'.po');
	}

	exec('msgfmt --o .i18n/'.$lang.'.mo .i18n/'.$lang.'.po');
}