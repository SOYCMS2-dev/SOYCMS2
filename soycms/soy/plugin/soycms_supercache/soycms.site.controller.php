<?php
class SOYCMS_SuperCachePlugin extends SOYCMS_SiteControllerExtension{
	
	private $isBuildCache = true;
	private $config = array();
	
	function prepare($controller){
		if(defined("SOYCMS_ADMIN_LOGINED"))return;
		if(defined("SOYCMS_USER_LOGINED") && SOYCMS_USER_LOGINED)return;
		
		//POST時はキャッシュを生成しない
		if(!empty($_POST)){
			$this->isBuildCache = false;
		}
		
		//キャッシュを表示して終了
		if(true === $this->output()){
			exit;
		}
		
		if($this->isBuildCache){
			ob_start();
			$this->config = SOYCMS_DataSets::get("soycms_supercache.config",array());
		}
	}
	
	function tearDown($controller){
		if(defined("SOYCMS_ADMIN_LOGINED"))return;
		if(defined("SOYCMS_USER_LOGINED") && SOYCMS_USER_LOGINED)return;
		
		if(!$this->isBuildCache)return;
		
		$pages = $this->config["pages"];
		if(!is_array($pages))$pages = array();
		
		$html = ob_get_contents();
		ob_end_clean();
		
		
		$cache_created = false;
		
		//キャッシュを生成するか判定
		$uri = SOYCMS_Helper::get("directory_uri");
		if(empty($uri))$uri = "_home";
		if(isset($pages[$uri])){
			if($pages[$uri]["active"] == 1){
				
				//ページ種別で判定
				$pageId = SOYCMS_Helper::get("page_id");
				$page = SOY2DAO::find("SOYCMS_Page",$pageId);
				
				if($page->getType() != "app" && $page->getType() != "search"){
					//create cache
					$this->createCache($html,(@$this->config["footprint"] > 0),$pages[$uri]["limit"]);
					$cache_created = true;
				}
			}
		}
		
		if(!$cache_created){
			echo $html;
			return;
		}
		
		
		ob_start();
			$ob = ob_start("ob_gzhandler");
			echo $html;
			if($ob) ob_end_flush();
			header("Content-Length: ".ob_get_length(), true);
		ob_end_flush();
		
	}
	
	function output(){
		$time = microtime(true);
		
		$cacheFilePath = $this->getCacheFilePath();
		$headerPath = $cacheFilePath . ".header";
		
		//キャッシュを出力するかどうか判定
		if(!file_exists($cacheFilePath)){
			return false;
		}
		if(!file_exists($headerPath)){
			return false;
		}
		
		//ヘッダー出力
		include($headerPath);
		if(!function_exists("soycms_supercache_header"))return false;
		$res = soycms_supercache_header();
		if(!$res)return;
		
		
		//キャッシュを出力する
		ob_start();
			$ob = ob_start("ob_gzhandler");
			readfile($cacheFilePath);
			$time = microtime(true) - $time;
			if(function_exists("soycms_supercache_footer"))soycms_supercache_footer($time);
		
			if($ob) ob_end_flush();
			header("Content-Length: ".ob_get_length(), true);
		ob_end_flush();
		
		return true;
	}
	
	function createCache($html, $footprint, $limit = 30){
		//キャッシュの保存
		$cacheFilePath = $this->getCacheFilePath();
		
		
		if(!file_exists(dirname($cacheFilePath))){
			soy2_mkdir(dirname($cacheFilePath));
		}
		file_put_contents($cacheFilePath,$html);
		
		$limitTime = time() + $limit * 60;
		
		//headerの保存
		$headerPath = $cacheFilePath . ".header";
		$headers = headers_list();
		$h = array();
		$h[] = 'function soycms_supercache_header(){';
		$h[] = 'if(time() > '.$limitTime.')return false;';
		
		foreach($headers as $header){
			if( stripos($header, "Content-Type:") === 0 ){
				$h[] = "header(\"{$header}\");";
			}
		}
		
		//生成日時
		$h[] = 'header("Cache-Control: max-age=" . ('.$limitTime.' - time()));';
		$h[] = 'if (session_id()) {';
		$h[] = 'header("Expires: " . gmdate("D, d M Y H:i:s T",'.$limitTime.'));';
		$h[] = 'header("Pragma: cache");';
		$h[] = '}';
		$h[] = "header(\"Last-Modified: ".gmdate("D, d M Y H:i:s T", filemtime($cacheFilePath))."\");";
		$h[] = "return true;";
		$h[] = "}";
		
		$h[] = 'function soycms_supercache_footer($time){';
		if($footprint){
			$h[] = 'echo "<!-- cache : {$time}sec.-->";';
		}
		$h[] = '}';
		
		//headerの保存
		file_put_contents($headerPath,"<?php\n".implode("\n", $h)."\n");
		
	}
	
	function getCacheFilePath(){
		$cacheDir = SOY2HTMLConfig::CacheDir() . "supercache/";
		$dirName = str_replace(array("/","\\"),"_",str_replace(soycms_get_site_path(),"",@$_SERVER["REQUEST_URI"]));
		$md5_pathinfo = md5(@$_SERVER["REQUEST_URI"]);
		$cache = "{$cacheDir}{$dirName}/{$md5_pathinfo[0]}/{$md5_pathinfo}.html";
		return $cache;
	}
	
}

PluginManager::extension("soycms.site.controller.prepare","soycms_supercache","SOYCMS_SuperCachePlugin");
PluginManager::extension("soycms.site.controller.teardown","soycms_supercache","SOYCMS_SuperCachePlugin");
?>
