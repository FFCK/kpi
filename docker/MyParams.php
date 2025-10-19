<?php
// Paramètres de connexion

define('URL_SITE', 'https://kpi.localhost');

define("PRODUCTION", FALSE);

// Prod
define('PARAM_PROD_LOGIN', 'root');
define('PARAM_PROD_PASSWORD', 'root');
define('PARAM_PROD_DB', 'my_database');
define('PARAM_PROD_SERVER', 'db');

// Mirror
define('PARAM_MIRROR_LOGIN', 'my_admin');
define('PARAM_MIRROR_PASSWORD', 'my_passwd');
define('PARAM_MIRROR_DB', 'my_database');
define('PARAM_MIRROR_SERVER', 'db');

// Local
define('PARAM_LOCAL_LOGIN', 'root');
define('PARAM_LOCAL_PASSWORD', 'root');
define('PARAM_LOCAL_DB', 'my_database');
define('PARAM_LOCAL_SERVER', 'db');


// Ftp
define('FTP_SERVER', '');
define('FTP_USER_NAME', '');
define('FTP_USER_PASS', '');


// Path
define('PATH_ABS', '/var/www/html/');	// Chemin Absolu	 
define('PATH_REL', './');				// Chemin Relatif 	
define('MAIN_DIRECTORY', '');

// Destinataires alertes cartons
define('CARD_ALERT_RECIPIENTS', '');
define('SEND_MAIL', true);

// Matomo Analytics
// Site ID pour la partie publique (frontend)
define('MATOMO_SITE_ID_PUBLIC', '3');
// Site ID pour la partie administration (backend)
define('MATOMO_SITE_ID_ADMIN', '3');
// URL du serveur Matomo
define('MATOMO_SERVER_URL', 'https://matomo.kayak-polo.info/');
