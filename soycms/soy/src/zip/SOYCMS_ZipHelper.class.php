<?php

class SOYCMS_ZipHelper {
	
	public static function check($flag = false){
		if(class_exists("ZipArchive"))return true;
		
		if($flag){
			return SOYCMS_CommonConfig::get("zip_support",false);
		}else{
			return SOYCMS_CommonConfig::get("unzip_support",false);
		}
		
	}

	public static function prepare($filepath){
		$helper = new SOYCMS_ZipHelper($filepath);
		return $helper;
	}
	
	private function SOYCMS_ZipHelper($filepath){
		$this->filepath = $filepath;
	}
	
	private $filepath;
	private $filter = null;
	private $items = array();
	
	/* 作成する */
	
	/**
	 * zipを作成する
	 */
	function compress(){
		
		if(file_exists($this->filepath)){
			unlink($this->filepath);
		}
		
		$filter = $this->filter;
		$tmpdir = soy2_realpath(dirname($this->filepath)) . str_replace(".zip","",basename($this->filepath)) . "/";
		
		if($filter){
			if(file_exists($tmpdir))soy2_delete_dir($tmpdir);
			soy2_mkdir($tmpdir);
		}
		
		//追加する要素
		$allItems = array();
		
		
		
		foreach($this->items as $array){
			$targetPath = $array[0];
			$filePath = $array[1];
			
			if(is_dir($filePath)){
				if($targetPath[strlen($targetPath)-1] != "/"){
					$targetPath .= "/";
				}
			}
			
			$items = $this->listAll($filePath);
			foreach($items as $src){
				
				$dst = str_replace($filePath, $targetPath, $src);
				
				$tmp_src = $tmpdir . $dst;
				soy2_copy($src, $tmp_src);
				$src = $tmp_src;
					
				if($filter){
					$this->doFilter($tmp_src);
				}
				
				//要素に追加
				$allItems[] = array($src,$dst);
			}
		}
		
		//圧縮
		if(class_exists("ZipArchive")){
			$zip = new ZipArchive();
			$res = $zip->open($this->filepath, ZipArchive::CREATE);
			
			if($res !== true){
				throw new Exception("failed");
			}
			
			foreach($allItems as $array){
				if(!file_exists($array[0]))continue;
				$zip->addFile($array[0],$array[1]);
			}
			$zip->close();
		
		}else if(SOYCMS_CommonConfig::get("zip_support",false)){
			$path = $this->filepath;
			$command = array();
			$command[] = "cd";
			$command[] = "\"$tmpdir\";";
			$command[] = "zip";
			$command[] = "-r";
			$command[] = "\"$path\"";
			$command[] = ".";
			$command = implode(" ",$command);
			exec($command);
		}
		
		if(file_exists($tmpdir))soy2_delete_dir($tmpdir);
	}
	
	/**
	 * 追加する
	 */
	function add($target,$filepath){
		$filepath = soy2_realpath($filepath);
		
		if($filepath){
			$this->items[$filepath] = array(
				$target,
				$filepath
			);
		}
		return $this;
	}
	
	/**
	 * 取り除く
	 */
	function remove($filepath = null){
		$filepath = soy2_realpath($filepath);
		if(isset($this->items[$filepath])){
			unset($this->items[$filepath]);
		}
	}
	
	private function listAll($filepath){
		if(!is_dir($filepath))return array($filepath);
		
		$res = array();
		$files = scandir($filepath);
		
		foreach($files as $file){
			if($file == "." || $file == "..")continue;
			
			if(is_dir($filepath . "/" . $file)){
				$res = array_merge($res,$this->listAll($filepath . "/" . $file));
			}else{
				$path = $filepath . "/" . $file;
				$res[] = soy2_realpath($path);
			}
		}
		
		return $res;
	}
	
	/* 解凍 */
	
	/**
	 * 解凍する
	 */
	function uncompress($target){
		
		if(file_exists($target)){
			soy2_delete_dir($target);
		}
		
		soy2_mkdir($target, 0666);
		
		if(class_exists("ZipArchive")){
			$zip = new ZipArchive();
			$res = $zip->open($this->filepath);
			
			if($res !== true){
				throw new Exception("failed");
			}
			
			$zip->extractTo($target);
			$zip->close();
			
		}else if(SOYCMS_CommonConfig::get("unzip_support",false)){
			$filepath = $this->filepath;
			$command = array();
			$command[] = "unzip";
			$command[] = "\"$filepath\"";
			$command[] = "-d";
			$command[] = "\"$target\"";
			$command = implode(" ",$command);
			exec($command);
		}
		
		$filter = $this->filter;
		if($filter){
			$this->doFilter($target);
		}
	}
	
	
	/* common */
	
	function doFilter($dir){
		if(!$this->filter)return;
		$dir = soy2_realpath($dir);
		
		if(!is_dir($dir)){
			return call_user_func($this->filter,$dir);
		}
		
		$files = scandir($dir);
		foreach($files as $file){
			if($file == "." || $file == "..")continue;
			$this->doFilter($dir. $file);
		}
	}
	
	/* getter setter */
	
	function getFilepath() {
		return $this->filepath;
	}
	function getFileName(){
		return basename($this->filepath);
	}
	function getFilter() {
		return $this->filter;
	}
	function setFilter($filter) {
		$this->filter = $filter;
	}
}
?>