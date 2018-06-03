<?php
// Configuration Generale 
define('NUM_VERSION','2.19.1');

require_once('MyParams.php');

if (strstr($_SERVER['DOCUMENT_ROOT'],'wamp') == false && strstr($_SERVER['HTTP_HOST'],'192.168') == false) {
	define("PRODUCTION", TRUE); // TRUE => Site de Production ...
} else {
	define("PRODUCTION", FALSE); // FALSE => localhost
    if (strstr($_SERVER['HTTP_HOST'],'192.168') == true){
        define("DEV", TRUE); // => Développement
    }
}

//define("PRODUCTION", FALSE);

define('FPDF_FONTPATH','font/');
