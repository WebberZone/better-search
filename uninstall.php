<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package BSearch
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;

// Delete Better Search table
$table_name = $wpdb->prefix . "bsearch";
$sql = "DROP TABLE $table_name";
$wpdb->query( $sql );

// Delete Better Daily Search table
$table_name = $wpdb->prefix . "bsearch_daily";
$sql = "DROP TABLE $table_name";
$wpdb->query( $sql );

// Drop FULLTEXT index
$wpdb->query( 'ALTER TABLE '.$wpdb->posts.' DROP INDEX bsearch ;' );

// Delete plugin options
delete_option('ald_bsearch_settings');
delete_option('bsearch_db_version');

?>