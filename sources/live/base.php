<?php
// Configuration generale et Connexion a la base de donnees ...
//include_once($_SERVER['DOCUMENT_ROOT'].'/adv/advUty.php');	
//include_once($_SERVER['DOCUMENT_ROOT'].'/adv/advBase.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');

class MyBase //extends advBase
{
	// Constructeur 
	function __construct($bConnect=true)
	{
		if (strstr($_SERVER['DOCUMENT_ROOT'],'devWeb_KPI') == false)
		{
			// Production ...
			$this->m_login = PARAM_PROD_LOGIN;
			$this->m_password = PARAM_PROD_PASSWORD;	
			$this->m_database = PARAM_PROD_DB;			  
			$this->m_server = PARAM_PROD_SERVER;
		}
		else
		{
			$this->m_login = PARAM_LOCAL_LOGIN;
			$this->m_password = PARAM_LOCAL_PASSWORD;	
			$this->m_database = PARAM_LOCAL_DB;			  
			$this->m_server = PARAM_LOCAL_SERVER;
		}
		
		$this->m_arrayUrlCache = array();

//		if ($bConnect) {
//            $this->Connect();
//        }
    }
}
