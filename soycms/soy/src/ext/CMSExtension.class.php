<?php
/**
 * CMSをフレームワークとして使う
 */
class CMSExtension {
	
	/**
	 * usual sigleton
	 */
	private static function getInstance(){
		static $_inst;
		if(!$_inst)$_inst = new CMSExtensionImpl();
		
		return $_inst;
	}
	
	/**
	 *
	 */
	public static function prepare($uri){
		self::getInstance()->prepare($uri);
	}
	
	/**
	* @param SOYCMS_Page $page
	* @param array $args
	*/
	public static function post($page,$args){
		self::getInstance()->post($page,$args);
	}
	
	/**
	 * @param SOYCMS_Page $page
	 * @param array $args
	 */
	public static function execute($page,$args){
		if(isset($_SERVER["REQUEST_METHOD"]) && strtolower($_SERVER["REQUEST_METHOD"]) == "post"){
			self::post($page,$args);
		}
		self::getInstance()->execute($page,$args);
	}
	
	/**
	* @param SOYCMS_Page $page
	* @param array $args
	*/
	public static function display($page,$webPage,$args){
		self::getInstance()->display($page,$webPage,$args);
	}
	
	/* config methods */
	
	/**
	 * set url mapping
	 */
	public static function mapping(){
		$args = func_get_args();
		if(count($args) < 1)return;
		if(count($args) > 1){
			if(!is_array($args[1]))return;
			$args = array(
				$args[0] => $args[1]
			);
		}else if(!is_array($args[0])){
			return;
		}else{
			$args = $args[0];
		}
		
		self::getInstance()->mapping($args);
	}
	
	/**
	 * set rule mapping
	 */
	public static function rule(){
		$args = func_get_args();
		if(count($args) > 1){
			$args = array(
				$args[0] => $args[1]
			);
		}
		
		self::getInstance()->rule($args);
	}
	
	public static function action(){
		$args = func_get_args();
		if(count($args) < 1)return;
		if(count($args) > 1){
			if(!is_array($args[1]))return;
			$args = array(
				$args[0] => $args[1]
			);
		}else if(!is_array($args[0])){
			return;
		}else{
			$args = $args[0];
		}
		
		self::getInstance()->action($args);
	}
	
	public static function filter(){
		$args = func_get_args();
		if(count($args) < 1)return;
		if(count($args) > 1){
			if(!is_array($args[1]))return;
			$args = array(
				$args[0] => $args[1]
			);
		}else if(!is_array($args[0])){
			return;
		}else{
			$args = $args[0];
		}
		
		self::getInstance()->filter($args);
	}
}


/**
 * CMS拡張機能実体
 */
class CMSExtensionImpl{
	
	private $ruleMappings = array();
	private $urlMappings = array();
	private $actions = array();
	private $filters = array();
	
	private $action = array();
	private $config = array(
		"title" => "",
		"template" => "",
		"flag" => true
	);
	
	/**
	 * @param string $uri
	 */
	public function prepare($uri){
		$uri = explode("?",$uri);
		$uri = array_shift($uri);
		$uri = implode("/",array_diff(explode("/", $uri),array("")));
		if(empty($uri))$uri = "_home";
		
		foreach($this->urlMappings as $url => $config){
			$url = str_replace("/","\\/",$url);
			
			if(preg_match("/^".$url."$/",$uri) || preg_match("/^".$url."$/",str_replace("index.html","",$uri))){
				
				if(isset($config["title"])){
					$this->config["title"] = $config["title"];
				}
				if(isset($config["template"])){
					$this->config["template"] = $config["template"];
					if(isset($config["flag"]))$this->config["flag"] = $config["flag"];
				}
				
				if(isset($config["action"])){
					$action_name = (is_array($config["action"])) ? $config["action"][0] : $config["action"];
					$args = (is_array($config["action"])) ? array_slice($config["action"], 1) : array();
					if(isset($this->actions[$action_name])){
						$this->config["flag"] = false;
						if(isset($config["flag"]))$this->config["flag"] = $config["flag"];
						$action_config = $this->actions[$action_name];
						$action = $action_config["class"];
						
						if(isset($action_config["path"])){
							SOY2::import(array($action_config["path"],$action));
						}else{
							SOY2::import($action);
						}
						$actions = explode(".",$action);
						$action_class = array_pop($actions);
						if(class_exists($action_class)){
							$this->action[$action_class] = new $action_class($args);
							SOY2::cast($this->action[$action_class],$config);
							$this->action[$action_class]->prepare($uri,$this);
							if(isset($action_config["last"]) && $action_config["last"] == true){
								break;
							}
						}
					}
				}
			}
		}
		
	}
	
	/**
	* @param SOYCMS_Page $page
	* @param array $args
	*/
	public function post($page,$args){
		
		if($this->action){
			foreach($this->action as $key => $obj){
				$obj->post($page,$args);
			}
		}
	}
	
	/**
	 * @param SOYCMS_Page $page
	 * @param array $args
	 */
	public function execute($page,$args){
		/* @var $pageObj SOYCMS_PageBase */
		$pageObj = $page->getPageObject();
		 
		if(!empty($this->config["title"])){
			$page->setConfigParam("title", $this->config["title"]);
		}
		
		if(!empty($this->config["template"])){
			$page->setTemplate($this->config["template"]);
		}
		
		if($this->action){
			foreach($this->action as $key => $obj){
				$obj->execute($page,$args);
			}
		}
	}
	
	/**
	* @param SOYCMS_Page $page
	* @param WebPage $webPage
	* @param array $args
	*/
	function display($page,$webPage,$args){
		if(!$this->config["flag"] && $webPage instanceof SOYCMS_ListPageBase){
			$webPage->setCheckLabel(false);
		}
		
		if($this->action){
			foreach($this->action as $key => $obj){
				$obj->display($page,$webPage,$args);
			}
		}
		
	}
	
	function mapping($array){
		$this->urlMappings = array_merge($this->urlMappings,$array);
	}
	
	function rule($array){
		$this->ruleMappings = array_merge($this->ruleMappings,$array);
	}
	
	function action($array){
		$this->actions = array_merge($this->actions,$array);
	}
	
	function filter($array){
		$this->filter = array_merge($this->filter,$array);
	}
	
	
	/* getter setter */

	public function getRuleMappings(){
		return $this->ruleMappings;
	}

	public function setRuleMappings($ruleMappings){
		$this->ruleMappings = $ruleMappings;
		return $this;
	}

	public function getUrlMappings(){
		return $this->urlMappings;
	}

	public function setUrlMappings($urlMappings){
		$this->urlMappings = $urlMappings;
		return $this;
	}

	public function getActions(){
		return $this->actions;
	}

	public function setActions($actions){
		$this->actions = $actions;
		return $this;
	}

	public function getFilters(){
		return $this->filters;
	}

	public function setFilters($filters){
		$this->filters = $filters;
		return $this;
	}
}
