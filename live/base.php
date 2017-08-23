<?php
// Configuration générale et Connexion à la base de données ...
include_once($_SERVER['DOCUMENT_ROOT'].'/adv/advUty.php');	
include_once($_SERVER['DOCUMENT_ROOT'].'/adv/advBase.php');

class MyBase extends advBase
{
	// Constructeur 
	function __construct($bConnect=true)
	{
		if (strstr($_SERVER['DOCUMENT_ROOT'],'devWeb_KPI') == false)
		{
			// Production ...
			$this->m_login = "poloweb4";
			$this->m_password = "5954yt05";	
			$this->m_database = "poloweb4";			  
			$this->m_server = "cl1-sql1";
		}
		else
		{
			$this->m_login = "root";
			$this->m_password = "";	
			$this->m_database = "poloweb4";			  
			$this->m_server = "localhost";
		}
		
		$this->m_arrayUrlCache = array();

		if ($bConnect)
			$this->Connect();
	}
}

?>
