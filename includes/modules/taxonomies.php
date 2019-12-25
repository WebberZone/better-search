<?php
/**
 * Taxonomies control module
 *
 * @package Better_Search
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Filter JOIN clause of bsearch query to add taxonomy tables.
 *
 * @since 2.4.0
 *
 * @param   mixed $join Join clause.
 * @return  string  Filtered JOIN clause
 */
function bsearch_exclude_categories_join( $join ) {
	global $wpdb, $bsearch_settings;

	if ( '' !== bsearch_get_option( 'exclude_categories' ) ) {

		$sql  = $join;
		$sql .= " LEFT JOIN $wpdb->term_relationships AS excat_tr ON ($wpdb->posts.ID = excat_tr.object_id) ";
		$sql .= " LEFT JOIN $wpdb->term_taxonomy AS excat_tt ON (excat_tr.term_taxonomy_id = excat_tt.term_taxonomy_id) ";

		return $sql;
	} else {
		return $join;
	}
}
add_filter( 'bsearch_posts_join', 'bsearch_exclude_categories_join' );


/**
 * Filter WHERE clause of bsearch query to exclude posts belonging to certain categories.
 *
 * @since 2.4.0
 *
 * @param   mixed $where WHERE clause.
 * @return  string  Filtered WHERE clause
 */
function bsearch_exclude_categories_where( $where ) {
	global $wpdb, $bsearch_settings;

	if ( '' === bsearch_get_option( 'exclude_categories' ) ) {
		return $where;
	} else {

		$terms = bsearch_get_option( 'exclude_categories' );

		$sql = $where;

		$sql .= " AND $wpdb->posts.ID NOT IN (
            SELECT object_id
            FROM $wpdb->term_relationships
            WHERE term_taxonomy_id IN ($terms)
        )";

		return $sql;
	}

}
add_filter( 'bsearch_posts_where', 'bsearch_exclude_categories_where' );


/**
 * Filter GROUP BY clause of bsearch query to exclude posts belonging to certain categories.
 *
 * @since 2.4.0
 *
 * @param   mixed $groupby GROUP BY clause.
 * @return  string  Filtered GROUP BY clause
 */
function bsearch_exclude_categories_groupby( $groupby ) {
	global $bsearch_settings;

	if ( '' !== bsearch_get_option( 'exclude_categories' ) && '' !== $groupby ) {

		$sql  = $groupby;
		$sql .= ' excat_tt.term_taxonomy_id ';

		return $sql;
	} else {
		return $groupby;
	}
}
add_filter( 'bsearch_posts_groupby', 'bsearch_exclude_categories_groupby' );

