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

$bsearch_settings = get_option( 'ald_bsearch_settings' );

if ( ! is_multisite() ) {

	$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch' );
	$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch_daily' );

	delete_option( 'ald_bsearch_settings' );

	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' );
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' );
	$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' );

	delete_option( 'bsearch_db_version' );

} else {

	// Get all blogs in the network and activate plugin on each one
	$blog_ids = $wpdb->get_col( "
        SELECT blog_id FROM $wpdb->blogs
        WHERE archived = '0' AND spam = '0' AND deleted = '0'
	" );

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );

		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch' );
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'bsearch_daily' );

		delete_option( 'ald_bsearch_settings' );

		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_title' );
		$wpdb->query( 'ALTER TABLE ' . $wpdb->posts . ' DROP INDEX bsearch_content' );

		delete_option( 'bsearch_db_version' );

	}

	// Switch back to the current blog
	restore_current_blog();

}
