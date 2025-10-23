<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Deconnexion

class Logout extends MyPage 
{	
	function __construct()
	{
		if(!isset($_SESSION)) {
			session_start(); 
		}
			
		if (isset($_SESSION['User']))
		{
			unset ($_SESSION['User']);
  			$_SESSION = array();
		}

		header("Location: /");	
	}		
}		  	

$page = new Logout();
