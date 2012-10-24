<?php

class page_index extends SOYCMS_WebPageBase{

	function page_index() {
		WebPage::WebPage();
		
		if(isset($_GET["init"])){
			
			$dao = SOY2DAOFactory::create("Plus_UserDAOBase");
			$sqls = explode(";",file_get_contents(PLUSUSER_ROOT_DIR . "src/sql/" . $_GET["init"] . ".sql"));
			 
			foreach($sqls as $sql){
				if(strlen($sql)<1)continue;
				try{
					$dao->executeUpdateQuery($sql);	
				}catch(Exception $e){
					echo $e->getPDOExceptionMessage();
					echo "<br />";
				}
			}
			
			if($_GET["init"]  == "tag"){
				
			}
			
			SOYCMS_DataSets::put("plus.user.config",null);
			
			exit;
			//$this->jump("");
			
		}
	}
}
?>