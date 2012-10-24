<?php

abstract class Plus_UserDAOBase extends SOY2DAO{
	
	function &getDataSource(){
		$database = PlusUserConfig::getConfig()->getDatabaseConfig();
		
		return SOY2DAO::_getDataSource(
			$database["dsn"],
			$database["user"],
			$database["password"]
		);
	}

}