<?php
// Configuration Generale 

//if (strstr($_SERVER['DOCUMENT_ROOT'],'192.168.174.134') == false)
//	define("PRODUCTION", TRUE); // TRUE => Site de Production ...
//else
//	define("PRODUCTION", FALSE); // FALSE => localhost Développement

define("PRODUCTION", FALSE);

if (PRODUCTION)
{		  
	// Site de Production
	define('PATH_ABS', '/home/users2/p/poloweb/www/agil/');		// Chemin Absolu	 
	define('PATH_REL', './');									// Chemin Relatif 	
	define('MAIN_DIRECTORY', '');
}
else
{						 
	// Site Local 
	define('PATH_ABS', '/var/www/html/kpi/');		// Chemin Absolu	 
	define('PATH_REL', './');				// Chemin Relatif 	 
	define('MAIN_DIRECTORY', '');
}
	
define('FPDF_FONTPATH','font/');

?>