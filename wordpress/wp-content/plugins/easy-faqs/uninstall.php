<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;

// delete easy faqs db version option
delete_option( 'easy_faqs_db_version' );

//drop easy FAQs stats table
$table_name = $wpdb->prefix . 'easy_faqs_search_log';
$delete_sql = sprintf('DROP TABLE IF EXISTS %s', $table_name);
$wpdb->query( $delete_sql );