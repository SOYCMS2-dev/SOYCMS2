<?php

class page_withdraw extends PlusUserWebPageBase{
	
	function doPost(){
		if(isset($_POST["user_password"])){
			$session = plus_user_get_session();
			
			/* @var $user Plus_User */
			$user = SOY2DAO::find("Plus_User",$session->getId());
			$config = PlusUserConfig::getConfig();
			
			if($user->checkPassword($_POST["user_password"])){
				$origin_user = clone($user);
				$groupIds = explode(",",$user->getGroupIds());
				
				//退会処理
				$user->setStatus(-1);	//退会に指定
				$user->setLoginId("deleted-" . md5($user->getLoginId() . "." . time()));
				$user->setMailAddress("deleted");
				$user->setName("deleted");
				$user->save();
				
				//clear all profile
				Plus_UserProfile::saveProfile($user->getId(), array());
				
				$session = SOY2Session::get("PlusUserSiteLoginSession");
				$session->deleteCookie();
				$session->destroy();
				
				//確認メールの送信
				$logic = SOY2Logic::createInstance("mail.SOYCMS_MailLogic");
				$title = null;
				$body = null;
				foreach($groupIds as $groupId){
					try{
						$title = SOYCMS_DataSets::get("plus.user.mail.{$groupId}.withdraw.title");
						$body = SOYCMS_DataSets::get("plus.user.mail.{$groupId}.withdraw.body");
						break;
					}catch(Exception $e){
						
					}
				}
				if($title && $body){
					$body = str_replace("#USERNAME#", $origin_user->getName(),$body);
									
					try{
						$logic->send(
							$origin_user->getMailAddress(),
							$title,
							$body
						);
					}catch(Exception $e){
						
					}
				}
				
				//退会の連携
				PluginManager::invoke("plus.user.withdraw",array(
					"userId" => $user->getId(),
					"user" => $origin_user
				));
				
				$url = $config->getModulePageUrl("plus_user_connector.withdraw","",array("complete" => $user->getId()));
				PlusUserApplicationHelper::getController()->jump($url);
				exit;
			
			}else{
				
				$url = $config->getModulePageUrl("plus_user_connector.withdraw","",array("invalid_password" => 1));
				PlusUserApplicationHelper::getController()->jump($url);
			}
		
		}
				
	}
	
	function init(){
		PluginManager::load("plus.user.withdraw");
		
		PlusUserApplicationHelper::putTopicPath("plus_user_connector.withdraw","登録情報の削除");
		
		if(!isset($_GET["complete"]) && !plus_user_get_user_id()){
			PlusUserApplicationHelper::getController()->jumpToLoginPage();
		}
	}

	function page_withdraw() {
		WebPage::WebPage();
	}
	
	function buildPage(){
		$this->addForm("withdraw_form",array(
			"soy2prefix" => "cms"
		));
		
		$this->addModel("mode_complete",array(
			"visible" => isset($_GET["complete"])
		));
		$this->addModel("mode_confirm",array(
			"visible" => !isset($_GET["complete"])
		));
		
		$this->addModel("password_error",array(
			"visible" => isset($_GET["invalid_password"])
		));
	}
	
	
}
?>