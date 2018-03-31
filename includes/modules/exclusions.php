<?php
/**
 * Exclusion functions used by Better Search
 *
 * @package Better_Search
 * @subpackage Better_Search/Exclusions
 */

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
add_filter( 'bsearch_posts_where', 'bsearch_exclude_protected', 11 );

