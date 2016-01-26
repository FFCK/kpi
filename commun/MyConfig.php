<?php
// Configuration Generale 

if (strstr($_SERVER['DOCUMENT_ROOT'],'wamp') == false)
	define("PRODUCTION", TRUE); // TRUE => Site de Production ...
else
	define("PRODUCTION", FALSE); // FALSE => localhost WampServer

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
	define('PATH_ABS', '/wamp/www/');		// Chemin Absolu	 
	define('PATH_REL', './');				// Chemin Relatif 	 
	define('MAIN_DIRECTORY', '');
}
	
define('FPDF_FONTPATH','font/');

?>