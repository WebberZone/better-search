<?php
/**
 * Exclusion functions used by Better Search
 *
 * @package Better_Search
 * @subpackage Better_Search/Exclusions
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Function to exclude protected posts.
 *
 * @since 2.2.0
 *
 * @param string $where WHERE clause.
 * @return string Updated WHERE clause
 */
function bsearch_exclude_protected( $where ) {
	global $wpdb;

	if ( bsearch_get_option( 'exclude_protected_posts' ) ) {
		$where .= " AND {$wpdb->posts}.post_password = '' ";
	}

	return $where;
}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_protected' );


/**
 * Function to exclude post IDs.
 *
 * @since 2.2.0
 *
 * @param string $where WHERE clause.
 * @return string Updated WHERE clause
 */
function bsearch_exclude_post_ids( $where ) {
	global $wpdb;

	$exclude_post_ids = bsearch_get_option( 'exclude_post_ids' );

	if ( ! empty( $exclude_post_ids ) ) {
		$where .= " AND {$wpdb->posts}.ID NOT IN ({$exclude_post_ids}) ";
	}

	return $where;
}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_post_ids' );

