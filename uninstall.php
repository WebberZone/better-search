<?php
if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
	global $wpdb;
   	$table_name = $wpdb->prefix . "bsearch";
	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);
   	$table_name = $wpdb->prefix . "bsearch_daily";
	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);
	
    $poststable = $wpdb->posts;

	$sql = "ALTER TABLE $poststable DROP INDEX bsearch";
	$wpdb->query($sql);
	
	$sql = "ALTER TABLE $poststable DROP INDEX bsearch_title";
	$wpdb->query($sql);
	
	$sql = "ALTER TABLE $poststable DROP INDEX bsearch_content";
	$wpdb->query($sql);
	
	
	delete_option('ald_bsearch_settings');
	delete_option('bsearch_db_version');
?>