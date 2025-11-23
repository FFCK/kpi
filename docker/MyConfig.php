<?php
// Configuration Generale 
define('NUM_VERSION', '5.38.113');

// Décalage horaire -35 minutes pour affichage des prochains matchs + match courant (kpmatchs.php)
define('DECALAGE_MINUTES', '-395 minutes');

// Composer autoloader
require_once(__DIR__ . '/../vendor/autoload.php');

require_once('MyParams.php');

define('FPDF_FONTPATH', 'font/');
