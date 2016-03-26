<?php
//LAURENT
if ( !session_id() ) {
  session_start();
}


/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clefs secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur 
 * {@link http://codex.wordpress.org/Editing_wp-config.php Modifier
 * wp-config.php} (en anglais). C'est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d'installation. Vous n'avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', '***');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', '***');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', '***');

/** Adresse de l'hébergement MySQL. */
//define('DB_HOST', 'cl1-sql1');
define('DB_HOST', 'localhost');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8');

/** Type de collation de la base de données. 
  * N'y touchez que si vous savez ce que vous faites. 
  */
define('DB_COLLATE', '');

/**#@+
 * Clefs uniques d'authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant 
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n'importe quel moment, afin d'invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'TV+u$(;UDYO^t {E::!uK*4$g_LK3vPgl{eL (~XhexI8 w<=c<9o?} HiHW54=U'); 
define('SECURE_AUTH_KEY',  'B89?Sb-(Pq5|#,nt%I2BEAb(Y6@f;{NI*e)mh! mZk^H1z7LZi8]w,S>Aq/M6b5m'); 
define('LOGGED_IN_KEY',    ';+K+~1jw+6cTasC8o6lEW)y$9-03#va%P|e^>4:Jw!c]8fR6p 2~Iid]:dz&G`h;'); 
define('NONCE_KEY',        '6<vAK FWp5Ecf-W_x/w4rRV-gR&j_ER(bAy%chu<O ..MNs0QxFL(qe5bN:&7h4q'); 
define('AUTH_SALT',        'dViMl_bO#&zyR<K04VjjwtNK.+kU5*>&u<p{3Hu=sX|M+x)RBt`6hj#]+`BL9I]:'); 
define('SECURE_AUTH_SALT', 'QV#B0|HPY1Vk})3i[;w?gRkOQS#Si+8YE_r,ad?J+zjV:vg;anl8(-2y8&`4(h)K'); 
define('LOGGED_IN_SALT',   '*Oh&FQ~`[rG~V8ZN31A|A_]|`BYk n?cJN~;Yk+Y?CBU!9q%/,ZGD;G{|`[E%m5!'); 
define('NONCE_SALT',       '[V<+(a>ZLte+xDm4,!4B}Xt-qjU1|DlO-+/G@:fp=|{A*`$o1{FM4evy5Z-&O2+s'); 
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique. 
 * N'utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés!
 */
$table_prefix  = 'wp_';

/**
 * Langue de localisation de WordPress, par défaut en Anglais.
 *
 * Modifiez cette valeur pour localiser WordPress. Un fichier MO correspondant
 * au langage choisi doit être installé dans le dossier wp-content/languages.
 * Par exemple, pour mettre en place une traduction française, mettez le fichier
 * fr_FR.mo dans wp-content/languages, et réglez l'option ci-dessous à "fr_FR".
 */
define('WPLANG', 'fr_FR');

/** 
 * Pour les développeurs : le mode deboguage de WordPress.
 * 
 * En passant la valeur suivante à "true", vous activez l'affichage des
 * notifications d'erreurs pendant votre essais.
 * Il est fortemment recommandé que les développeurs d'extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de 
 * développement.
 */ 
define('WP_DEBUG', false); 

/* C'est tout, ne touchez pas à ce qui suit ! Bon blogging ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');