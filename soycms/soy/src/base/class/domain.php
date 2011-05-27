<?php
/**
 * ファイルベースのインターフェイス
 */
interface SerialziedEntityInterface{
	
	//全て取得
	public static function getList();
	
	//読み込み
	public static function load($param);
	
	//保存
	function save();
	
}
