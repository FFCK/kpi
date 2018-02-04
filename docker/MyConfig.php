<?php
// Configuration Generale 
define('NUM_VERSION','2.13.4');

if (strstr($_SERVER['DOCUMENT_ROOT'],'wamp') == false && strstr($_SERVER['HTTP_HOST'],'192.168') == false) {
	define("PRODUCTION", TRUE); // TRUE => Site de Production ...
} else {
	define("PRODUCTION", FALSE); // FALSE => localhost
    if (strstr($_SERVER['HTTP_HOST'],'192.168') == true){
        define("DEV", TRUE); // => Développement
    }
}

//define("PRODUCTION", FALSE);

if (PRODUCTION) {		  
	// Site de Production
	define('PATH_ABS', '/var/www/html/');		// Chemin Absolu	 
	define('PATH_REL', './');									// Chemin Relatif 	
	define('MAIN_DIRECTORY', '');
} elseif(DEV) {						 
	// Site Développement 
	define('PATH_ABS', '/var/www/html/kpi/');		// Chemin Absolu	 
	define('PATH_REL', './');				// Chemin Relatif 	 
	define('MAIN_DIRECTORY', '');
} else {
	// Mode local 
	define('PATH_ABS', '/wamp/www/kpi/');		// Chemin Absolu	 
	define('PATH_REL', './');				// Chemin Relatif 	 
	define('MAIN_DIRECTORY', '');
}
	
define('FPDF_FONTPATH','font/');
