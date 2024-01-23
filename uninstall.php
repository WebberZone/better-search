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

	$sites = get_sites(
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
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

	if ( defined( 'BETTER_SEARCH_DELETE_DATA' ) && BETTER_SEARCH_DELETE_DATA ) {
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch_daily' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	delete_option( 'ald_bsearch_settings' );
	delete_option( 'bsearch_settings' );
	delete_option( 'bsearch_db_version' );

	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery

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
