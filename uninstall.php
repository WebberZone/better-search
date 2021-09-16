<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

global $wpdb;

if ( is_multisite() ) {

	// Get all blogs in the network and activate plugin on each one.
	$blog_ids = $wpdb->get_col( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		"
        SELECT blog_id FROM $wpdb->blogs
        WHERE archived = '0' AND spam = '0' AND deleted = '0'
	"
	);

	foreach ( $blog_ids as $blogid ) {
		switch_to_blog( $blogid );
		bsearch_delete_data();
		restore_current_blog();
	}
} else {
	bsearch_delete_data();
}


/**
 * Delete plugin data.
 *
 * @since 2.5.0
 */
function bsearch_delete_data() {
	global $wpdb;

	$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch_daily' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

	delete_option( 'ald_bsearch_settings' );
	delete_option( 'bsearch_settings' );

	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

	delete_option( 'bsearch_db_version' );

	$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_bs_%'
	";

	$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	if ( is_array( $results ) ) {
		foreach ( $results as $result ) {
			$transient = str_replace( '_transient_', '', $result->option_name );
			delete_transient( $transient );
		}
	}
}
