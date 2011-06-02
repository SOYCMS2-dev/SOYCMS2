<?php
SOY2::imports("site.domain.block.*");

/**
 * Blockのサブクラスはsite/domain/block以下に配置
 */
class SOYCMS_Block {
	
	/**
	 * ファイルを指定して読み込み
	 */
	public static function load($id,$dir){
		$id = str_replace(array(".","-","/"),"_",$id);
		
		$filepath = $dir . $id . ".block";
		
		if(!file_exists($filepath)){
			return null;
		}
		
		$file = file_get_contents($filepath);
		
		$obj = soy2_unserialize($file);
		if($obj instanceof SOYCMS_Block){
			$obj->setId($id);
			$obj->setPath($filepath);
			return $obj;
		}
		
		return SOYCMS_Block::create($id,$filepath);
	}
	
	/**
	 * 作成
	 */
	public static function create($id, $dirname = null, $options = array()){
		if(!$dirname){
			$dirname = SOYCMS_SITE_DIRECTORY . ".page/";
		}
		
		$id = str_replace(array(".","-","/"),"_",$id);
		$filepath = $dirname . $id . ".block";
		
		$obj = new SOYCMS_Block();
		$obj->setId($id);
		$obj->setPath($filepath);
		if(isset($options["type"]))$obj->setType($options["type"]);
		
		return $obj;	
	}
	
	/**
	 * 保存
	 */
	function save(){
		if(!file_exists(dirname($this->getPath())))mkdir(dirname($this->getPath()));
		
		//作成
		if(!file_exists($this->getPath())){
			$this->getObject()->onCreate();
		}else{
			$this->getObject()->onSave();
		}
		
		file_put_contents($this->getPath(),soy2_serialize($this));
	}
	
	/**
	 * 削除
	 */
	function delete(){
		$this->getObject()->onDelete();
		@unlink($this->getPath());	
	}
	
	/**
	 * プレビューを取得
	 */
	function getPreview(){
		$html = array();
		$html[] = '<div class="section">';
		$html[] = '	<h2><!-- cms:id="block_name" -->名前<!-- /cms:id="block_name" --></h2>';
		$html[] = '	<dl class="archive-list">';
		$html[] = '		<!-- cms:id="entry_list" -->';
		$html[] = '		<dt cms:id="create_date" cms:format="Y.m.d">2010.04.20</dt>';
		$html[] = '		<dd><a cms:id="entry_link"><!-- cms:id="title" -->テキストが入ります<!--/cms:id="title" --></a></dd>';
		$html[] = '		<!-- /cms:id="entry_list" -->';
		$html[] = '	</dl>';
		$html[] = '	<p class="link-r"><a cms:id="block_index_link"><!-- cms:id="block_index_title" -->一覧用名前<!-- /cms:id="block_index_title" -->の一覧</a></p>';
		$html[] = '</div>';
		
		return implode("\n",$html);
	}
	
	/* getter setter */
	
	private $id;
	
	private $name;
	
	private $description;
	
	private $indexTitle = "#BlockName#";
	private $indexUrl = "#DirUrl#";
	
	private $path;
	
	private $type = "directory";	//ブロック種別
	
	private $object; //実際のインスタンス
	
	private $config; //Config
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$id = str_replace(array(".","/","-"),"_",$id);
		$this->id = $id;
	}
	function getType() {
		if(strlen($this->type)<1)return "label";
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getObject() {
		if(!$this->object){
			$class = "SOYCMS_Block_".ucwords($this->getType())."BlockComponent";
			if(!class_exists($class))$class = "SOYCMS_Block_BlockComponentBase";
			$this->object = new $class;
		}
		
		if($this->object instanceof SOYCMS_Block_BlockComponentBase){
		
			$this->object->setBlockId($this->getId());
			
			return $this->object;
		}
		
		return new SOYCMS_Block_BlockComponentBase();
	}
	
	function setObject($object) {
		if(is_array($object)){
			SOY2::cast($this->object,(object)$object);
		}else{
			$this->object = $object;
		}
	}

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	
	function getName() {
		if(strlen($this->name)<1)return $this->getId();
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}

	function getPath() {
		return $this->path;
	}
	function setPath($path) {
		$this->path = $path;
	}

	function getIndexTitle() {
		return $this->indexTitle;
	}
	function setIndexTitle($indexTitle) {
		$this->indexTitle = $indexTitle;
	}
	function getIndexUrl() {
		return $this->indexUrl;
	}
	function setIndexUrl($indexUrl) {
		$this->indexUrl = $indexUrl;
	}
}

/**
 * ブロックのベース
 */
class SOYCMS_Block_BlockComponentBase{
	
	private $blockId;
	
	/**
	 * 一覧画面で表示
	 */
	function getPreview(){
		
	}
	
	/**
	 * 詳細画面で表示
	 */
	function getForm(){
		
	}
	
	/**
	 * 公開側で使用
	 */
	function execute($blockComponent,$pageObject){
		
		//高速化対策、非表示の時は記事を取得しない
		$blockComponent->createAdd("entry_list","SOYCMS_EntryListComponent",array(
			"list" => ($blockComponent->getVisible()) ? $this->getEntries() : array(),
			"soy2prefix" => "cms",
			"mode" => "block"
		));
		
	}
	
	/**
	 * 公開側で使う
	 */
	function getEntries(){
		return array();
	}
	
	/**
	 * create時に呼び出し
	 */
	function onCreate(){
		
	}
	
	/**
	 * save時に呼び出し
	 */
	function onSave(){
		
	}
	
	/**
	 * delete時に呼び出し
	 */
	function onDelete(){
		
	}

	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
}
?>