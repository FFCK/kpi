<?php
global $easy_faqs_db_version;
$easy_faqs_db_version = '1.2';

function easy_faqs_db_install() {
	global $wpdb;
	global $easy_faqs_db_version;

	$table_name = $wpdb->prefix . 'easy_faqs_search_log';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		query varchar(255) DEFAULT '' NOT NULL,
		friendly_location varchar(55) DEFAULT '' NOT NULL,
		ip_address text NOT NULL,
		result_count int(11) DEFAULT 0 NOT NULL,
		PRIMARY KEY  id (id),
		UNIQUE KEY id_query (id, query)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'easy_faqs_db_version', $easy_faqs_db_version );
}

function easy_faqs_update_db_check() {
    global $easy_faqs_db_version;
    if ( get_site_option( 'easy_faqs_db_version' ) != $easy_faqs_db_version ) {
        easy_faqs_db_install();
    }
}

if (isValidFAQKey()) {
	add_action( 'plugins_loaded', 'easy_faqs_update_db_check' );
}