<?php
class page_login extends PlusUserWebPageBase{
	
	function doPost(){
		
		$this->id = @$_POST["id"];
		$this->password = @$_POST["password"];
		
		if($this->id && $this->password){
			$this->id = @$_POST["id"];
			$this->password = $_POST["password"];
			
			try{
				$userDAO = SOY2DAOFactory::create("Plus_UserDAO");
				$user = $userDAO->getByLoginId($this->id);
				if($user->isActive() && $user->checkPassword($this->password)){
					
					/* @var $session PlusUserSiteLoginSession */
					$session = SOY2Session::get("PlusUserSiteLoginSession");
					$session->login($user);
					session_regenerate_id();
					
					//cookie login
					
					if(isset($_POST["autologin"])){
						try{
							$session->publishCookie();
						}catch(Exception $e){
							//do nothing
						}
					}
					
					if(isset($_GET["return_path"])){
						PlusUserApplicationHelper::getController()->jump(soycms_get_page_url($_GET["return_path"]));
					}else{
						PlusUserApplicationHelper::getController()->jumpToTop();
					}
					
					exit;
				}
			}catch(Exception $e){
				
			}
		}
		
		$this->error = true;
		if(isset($_POST["return_path"])){
			$url = soycms_get_page_url($_POST["return_path"]);
			$url .= "?failed&id=" . rawurlencode($this->id);
			PlusUserApplicationHelper::getController()->jump($url);
			exit;
		}
		
	}
	
	private $id;
	private $password;
	private $error = false;
	private $config = null;
	
	function init(){
		$session = plus_user_get_session();
		if($session->isLoggedIn()){
			PlusUserApplicationHelper::getController()->jumpToTop();
		}
		
		PlusUserApplicationHelper::putModuleTopicPath("plus_user_connector.login","ログイン");
	}

	function page_login() {
		WebPage::WebPage();
	}
	
	function buildPage(){
		
		$this->addModel("login_error",array(
			"visible" => $this->error,
			"soy2prefix" => "cms"
		));
		
		$this->addForm("login_form",array(
			"soy2prefix" => "cms"
		));
		
		$this->addInput("login_id",array(
			"name" => "id",
			"value" => $this->id,
			"soy2prefix" => "cms"
		));
		
		$this->addInput("login_password",array(
			"name" => "password",
			"value" => $this->password,
			"soy2prefix" => "cms"
		));
		
		$this->addModel("is_allowed_register",array(
			"visible" => $this->config->isModuleActive("plus_user_connector.register"),
			"soy2prefix" => "cms"
		));
		
		$this->addLink("register_link",array(
			"soy2prefix" => "cms",
			"link" => $this->config->getModulePageUrl("plus_user_connector.register")
		));
		
		$this->addInput("remeber_login",array(
			"name" => "autologin",
			"value" => 1,
			"attr:checked" => 1,
			"soy2prefix" => "cms",
		));
	}
	
	

	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
}
?>