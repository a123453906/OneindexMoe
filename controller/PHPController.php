<?php 
define('VIEW_PATH', ROOT.'view/admin/');
class PHPController{
	
	
	function __construct(){
	}

	function info(){
		return phpinfo();
	}

}
