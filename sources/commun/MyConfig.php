<?php
// Configuration Generale 
define('NUM_VERSION', '5.11.0');

// Décalage horaire -35 minutes pour affichage des prochains matchs + match courant (kpmatchs.php)
define('DECALAGE_MINUTES', '-395 minutes');

require_once('MyParams.php');

if (
    strstr($_SERVER['DOCUMENT_ROOT'], 'wamp') == false
    && strstr($_SERVER['HTTP_HOST'], '192.168.') == false
    && strstr($_SERVER['HTTP_HOST'], '172.') == false
) {
    define("PRODUCTION", TRUE); // TRUE => Site de Production ...
} else {
    define("PRODUCTION", FALSE); // FALSE => localhost
    if (strstr($_SERVER['HTTP_HOST'], '192.168') == true) {
        define("DEV", TRUE); // => Développement
    }
}

//define("PRODUCTION", FALSE);

define('FPDF_FONTPATH', 'font/');
